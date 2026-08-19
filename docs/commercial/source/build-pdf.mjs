import { chromium } from 'playwright-core';
const EXEC = '/Users/hom/Library/Caches/ms-playwright/chromium-1217/chrome-mac-arm64/Google Chrome for Testing.app/Contents/MacOS/Google Chrome for Testing';
const DIR = '/private/tmp/claude-501/-Applications-MAMP-htdocs-optimiz/b9a36b50-c0c3-4227-a155-8c3346fa905d/scratchpad/brochure';
const OUT = process.argv[2] || `${DIR}/OptimiZe-Brochure.pdf`;

const browser = await chromium.launch({ executablePath: EXEC });
const page = await browser.newPage();
await page.goto('file://' + DIR + '/index.html', { waitUntil: 'networkidle' });
await page.evaluate(() => document.fonts.ready);
await page.waitForTimeout(900);

// overflow audit: does any page's content exceed its 297mm box?
const audit = await page.evaluate(() => {
  const MM = 297 * (96/25.4);
  return [...document.querySelectorAll('.page')].map((p, i) => {
    const pTop = p.getBoundingClientRect().top;
    let max = 0, worst = '';
    p.querySelectorAll('*').forEach(el => {
      const r = el.getBoundingClientRect();
      if (r.height === 0) return;
      if (el.closest('.shot')) return;   // images intentionally clipped by their crop box
      const b = r.bottom - pTop;
      if (b > max) { max = b; worst = (el.className && typeof el.className === 'string' ? el.className : el.tagName); }
    });
    const mm = max / (96/25.4);
    return { page: i+1, contentBottom_mm: +mm.toFixed(1), overflow_mm: +(mm - 297).toFixed(1), worst };
  });
});
console.table(audit);
const collide = await page.evaluate(() => {
  const out=[];
  document.querySelectorAll('.page').forEach((p,i)=>{
    const pad=p.querySelector('.pad'), ov=p.querySelector('.contact, .cta, .ftr');
    if(!pad||!ov) return;
    const gap=(ov.getBoundingClientRect().top - pad.getBoundingClientRect().bottom)/(96/25.4);
    out.push({page:i+1, gap_before_overlay_mm:+gap.toFixed(1), status: gap<0?'*** COLLISION ***':'ok'});
  });
  return out;
});
console.table(collide);

await page.pdf({ path: OUT, width: '210mm', height: '297mm', printBackground: true,
                 margin: {top:'0',right:'0',bottom:'0',left:'0'}, preferCSSPageSize: true });

// also render each page as PNG for visual QA
await page.setViewportSize({ width: 794, height: 1123 });
const n = await page.evaluate(() => document.querySelectorAll('.page').length);
for (let i = 0; i < n; i++) {
  const el = page.locator('.page').nth(i);
  await el.screenshot({ path: `${DIR}/qa-${i+1}.png`, scale: 'css' });
}
console.log('pages:', n, '->', OUT);
await browser.close();
