import fs from 'node:fs/promises';
import path from 'node:path';
import {pathToFileURL} from 'node:url';
import puppeteer from 'puppeteer-core';

const out='docs/screenshots/nov8-seven-fixes';
const cases=[
 ['receipt-ownership','1. Receipt ownership','Another learner’s receipt was visible.','Access denied (403); owners retain access.'],
 ['branch-receipt','1. Receipt branch restriction','A secretary could view another branch’s payment proof.','Other-branch proof is denied (403).'],
 ['secretary-permissions','2. Secretary permissions','School settings were editable by branch secretaries.','School-wide settings, course management and financial reports require a school administrator.'],
 ['enrollment-detail','3. Enrollment detail','The detail link returned a server error (500).','The verification dashboard opens successfully.'],
 ['progress-edit','4. Instructor progress','The edit page returned a server error (500).','The edit form loads; saved changes persist.'],
 ['admin-lessons','5. Administrator lessons','Create Lesson returned access denied (403).','Administrators can create, edit and delete lessons through admin routes.'],
 ['duplicate-course','6. Duplicate course title','A duplicate title caused a server error (500).','The title field shows a validation error (422).'],
 ['system-password-reset','7. System administrator recovery','Requesting a reset returned a server error (500).','Reset requests succeed; token validation and password updates pass.'],
];
const css=`*{box-sizing:border-box}body{margin:0;background:#f1f5f9;color:#172033;font:16px Arial,sans-serif}header{padding:32px}h1{margin:0 0 12px}section{padding:24px;margin:0 0 24px;background:white}h2{margin:0 0 20px;font-size:24px}.pair{display:grid;grid-template-columns:1fr 1fr;gap:20px}.label{font-size:18px;font-weight:bold;margin:0 0 8px}.caption{height:44px;line-height:21px;margin:0 0 12px}img{display:block;width:100%;border:1px solid #cbd5e1}a{color:#174f9e}`;
const section=([key,title,before,after])=>`<section id="${key}"><h2>${title}</h2><div class="pair"><div><p class="label">Before</p><p class="caption">${before}</p><a href="${key}-before.png"><img src="${key}-before.png" alt="${title} before"></a></div><div><p class="label">After</p><p class="caption">${after}</p><a href="${key}-after.png"><img src="${key}-after.png" alt="${title} after"></a></div></div></section>`;
await fs.writeFile(out+'/index.html',`<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Seven fixes — before and after</title><style>${css}</style><header><h1>Seven fixes: before and after</h1><p>November 8, 2026 · Before: commit 7d42f0e · After: working fixes · Matched 1440 × 1000 browser captures.</p><p>Tests ran against an isolated database copy. Receipt images are labeled QA samples. Click an image to inspect its original resolution.</p><a href="README.md">Test report</a></header>${cases.map(section).join('')}</html>`);
const browser=await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--no-sandbox'],defaultViewport:{width:1600,height:1200}});
try{
 for(const item of cases){
  const page=await browser.newPage();
  await page.goto(pathToFileURL(path.resolve(out,'index.html')).href,{waitUntil:'load'});
  await page.evaluate(key=>{document.querySelector('header').remove();document.querySelectorAll('section').forEach(el=>{if(el.id!==key)el.remove();});},item[0]);
  await page.evaluate(()=>Promise.all([...document.images].map(img=>img.decode())));
  await page.setViewport({width:1600,height:Math.ceil(await page.$eval('section',el=>el.getBoundingClientRect().height))});
  await new Promise(resolve=>setTimeout(resolve,150));
  await page.screenshot({path:`${out}/${item[0]}-comparison.png`,fullPage:true});
  await page.close();
 }
}finally{await browser.close();}
console.log('Built eight labeled before/after comparisons and the complete gallery.');
