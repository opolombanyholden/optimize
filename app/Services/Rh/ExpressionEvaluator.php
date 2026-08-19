<?php

namespace App\Services\Rh;

/**
 * Évaluateur d'expressions arithmétiques sécurisé.
 *
 * Approche : tokenisation + Shunting Yard (postfix) + pile.
 * AUCUN appel à eval() ou create_function(). Seules les opérations explicitement supportées sont exécutées.
 *
 * Opérateurs supportés : + - * / et parenthèses
 * Fonctions supportées : max(a,b), min(a,b), round(x), abs(x), floor(x), ceil(x)
 * Variables : remplacées en amont par leur valeur numérique
 *
 * Toute syntaxe inconnue lève InvalidArgumentException.
 */
class ExpressionEvaluator
{
    private const FUNCTIONS = ['max', 'min', 'round', 'abs', 'floor', 'ceil'];
    private const OPERATORS = ['+' => 1, '-' => 1, '*' => 2, '/' => 2];

    public function compute(string $expression): float
    {
        $tokens = $this->tokenize($expression);
        $postfix = $this->toPostfix($tokens);
        return $this->reducePostfix($postfix);
    }

    private function tokenize(string $expr): array
    {
        $expr = str_replace(' ', '', $expr);
        $tokens = [];
        $i = 0;
        $len = strlen($expr);
        while ($i < $len) {
            $c = $expr[$i];

            if (ctype_digit($c) || $c === '.') {
                $num = '';
                while ($i < $len && (ctype_digit($expr[$i]) || $expr[$i] === '.')) {
                    $num .= $expr[$i++];
                }
                $tokens[] = ['type' => 'num', 'value' => (float) $num];
                continue;
            }

            if (ctype_alpha($c)) {
                $id = '';
                while ($i < $len && ctype_alpha($expr[$i])) {
                    $id .= $expr[$i++];
                }
                if (!in_array($id, self::FUNCTIONS, true)) {
                    throw new \InvalidArgumentException("Fonction inconnue: $id");
                }
                $tokens[] = ['type' => 'func', 'value' => $id];
                continue;
            }

            if (isset(self::OPERATORS[$c])) {
                if ($c === '-' && (count($tokens) === 0 || in_array(end($tokens)['type'], ['op', 'lparen', 'comma']))) {
                    $tokens[] = ['type' => 'num', 'value' => 0.0];
                }
                $tokens[] = ['type' => 'op', 'value' => $c];
                $i++;
                continue;
            }
            if ($c === '(') { $tokens[] = ['type' => 'lparen']; $i++; continue; }
            if ($c === ')') { $tokens[] = ['type' => 'rparen']; $i++; continue; }
            if ($c === ',') { $tokens[] = ['type' => 'comma']; $i++; continue; }

            throw new \InvalidArgumentException("Caractère interdit dans la formule: '$c'");
        }
        return $tokens;
    }

    private function toPostfix(array $tokens): array
    {
        $output = [];
        $stack = [];
        foreach ($tokens as $t) {
            if ($t['type'] === 'num') {
                $output[] = $t;
            } elseif ($t['type'] === 'func') {
                $stack[] = $t;
            } elseif ($t['type'] === 'comma') {
                while (!empty($stack) && end($stack)['type'] !== 'lparen') {
                    $output[] = array_pop($stack);
                }
            } elseif ($t['type'] === 'op') {
                while (
                    !empty($stack)
                    && end($stack)['type'] === 'op'
                    && self::OPERATORS[end($stack)['value']] >= self::OPERATORS[$t['value']]
                ) {
                    $output[] = array_pop($stack);
                }
                $stack[] = $t;
            } elseif ($t['type'] === 'lparen') {
                $stack[] = $t;
            } elseif ($t['type'] === 'rparen') {
                while (!empty($stack) && end($stack)['type'] !== 'lparen') {
                    $output[] = array_pop($stack);
                }
                if (empty($stack)) {
                    throw new \InvalidArgumentException('Parenthèses déséquilibrées');
                }
                array_pop($stack);
                if (!empty($stack) && end($stack)['type'] === 'func') {
                    $output[] = array_pop($stack);
                }
            }
        }
        while (!empty($stack)) {
            $top = array_pop($stack);
            if (in_array($top['type'], ['lparen', 'rparen'])) {
                throw new \InvalidArgumentException('Parenthèses déséquilibrées');
            }
            $output[] = $top;
        }
        return $output;
    }

    private function reducePostfix(array $postfix): float
    {
        $stack = [];
        foreach ($postfix as $t) {
            if ($t['type'] === 'num') {
                $stack[] = $t['value'];
            } elseif ($t['type'] === 'op') {
                if (count($stack) < 2) {
                    throw new \InvalidArgumentException('Expression invalide');
                }
                $b = array_pop($stack);
                $a = array_pop($stack);
                $stack[] = match ($t['value']) {
                    '+' => $a + $b,
                    '-' => $a - $b,
                    '*' => $a * $b,
                    '/' => $b == 0 ? 0 : $a / $b,
                };
            } elseif ($t['type'] === 'func') {
                $stack[] = match ($t['value']) {
                    'round' => round(array_pop($stack), 2),
                    'abs'   => abs(array_pop($stack)),
                    'floor' => floor(array_pop($stack)),
                    'ceil'  => ceil(array_pop($stack)),
                    'max'   => $this->popTwoAnd($stack, fn($a, $b) => max($a, $b)),
                    'min'   => $this->popTwoAnd($stack, fn($a, $b) => min($a, $b)),
                };
            }
        }
        if (count($stack) !== 1) {
            throw new \InvalidArgumentException('Expression invalide');
        }
        return (float) $stack[0];
    }

    private function popTwoAnd(array &$stack, callable $fn): float
    {
        $b = array_pop($stack);
        $a = array_pop($stack);
        return $fn($a, $b);
    }
}
