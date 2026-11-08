import puppeteer from 'puppeteer-core';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import {fileURLToPath} from 'node:url';
import {execFileSync} from 'node:child_process';

const output = new URL('../docs/screenshots/nov7-before-after/',import.meta.url);
await fs.mkdir(output,{recursive:true});
const browser = await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--no-sandbox'],defaultViewport:{width:1440,height:1000}});
let fixture;
const records = [];
const errors = [];
const cases = [
 ['payments-desktop','Student payment receipts','Receipt buttons are available beside each payment.'],
 ['payments-mobile','Student payment receipts — mobile','Receipt actions remain available in mobile payment cards.'],
 ['guest-desktop','Guest enrollment payment details','Submitted amounts, methods, references, statuses and receipts appear on the request.'],
 ['guest-mobile','Guest payment details — mobile','The submitted-payment section fits a phone screen.'],
 ['profile-desktop','User documents and activity','The profile includes submitted licenses, enrollment history and payment receipts.'],
 ['profile-mobile','User documents and activity — mobile','The profile scrolls within the viewport and keeps its footer visible.'],
 ['pickup-desktop','Pickup notice dialog','The dialog opens in the center of the viewport.'],
 ['pickup-mobile','Pickup notice dialog — mobile','The dialog fits a narrow screen.'],
 ['payment-summary','Payment summary on page 2','Totals and status counts remain accurate after pagination.'],
 ['license-viewer','License image viewer','License inspection uses the shared zoom, pan, rotate and download viewer.'],
 ['receipt-viewer','GCash receipt image viewer','A receipt opens in the standard image viewer instead of the payment QR viewer.'],
 ['missing-image','Missing receipt image','Failed images show an unavailable-image message instead of an empty QR payment popup.'],
 ['enrollment-actions','Enrollment action buttons','Actions wrap within their table cell.'],
 ['enrollment-pagination','Enrollment pagination','Enrollment uses the shared numbered pagination design.'],
 ['payment-pagination','Payment pagination','Payment pagination uses the same control styling.'],
];
const pause = ms => new Promise(resolve=>setTimeout(resolve,ms));
try {
 fixture = JSON.parse(execFileSync('docker',['exec','--user','www-data','driveed_hub_app','php','tests/nov7-browser-fixture.php'],{encoding:'utf8'}));
 await fs.mkdir(new URL('../tmp/nov7-before/storage/app/private/receipts/',import.meta.url),{recursive:true});
 execFileSync('docker',['cp','driveed_hub_app:/var/www/html/storage/app/private/receipts/qa-nov7-browser.png',fileURLToPath(new URL('../tmp/nov7-before/storage/app/private/receipts/qa-nov7-browser.png',import.meta.url))]);
 for (const version of ['before','after']) {
  const port = version==='before' ? 8005 : 8004;
  const root = `http://localhost:${port}/drived-hub`;
  const receiptUrl = fixture.receipt_url.replace(':8004',`:${port}`);
  const context = await browser.createBrowserContext();
  const visit = (page,path) => page.goto(root+'/'+path,{waitUntil:'networkidle2'});
  const shot = async(page,name) => {
   await pause(350);
   await page.screenshot({path:fileURLToPath(new URL(`${name}-${version}.png`,output)),fullPage:false});
   console.log(`CAPTURED: ${version} ${name}`);
  };
  async function login(role,temporary=false) {
   const page = await context.newPage();
   page.on('pageerror',e=>errors.push({version,message:e.message}));
   await visit(page,'login');
   await page.type('[name=email]',temporary ? `qa-nov7-${role}@example.invalid` : 'admin1@driveedhub.test');
   await page.type('[name=password]',temporary ? 'Nov7-Local-Test-123!' : 'DriveDemo123');
   await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),page.click('button[type=submit]')]);
   assert(page.url().includes('/'+role), `${version} login ended at ${page.url()}: ${(await page.$eval('body',el=>el.innerText)).slice(-900)}`);
   return page;
  }
  // Each role uses its own context because the app stores all guards in one session.
  const admin = await login('admin');
  await visit(admin,'admin/payments?page=2');
  const summary = await admin.$$eval('.stat-value',els=>els.map(el=>el.textContent.trim()));
  await shot(admin,'payment-summary');
  records.push({version,summary});
  await admin.evaluate(()=>window.scrollTo(0,document.documentElement.scrollHeight));
  await shot(admin,'payment-pagination');

  await visit(admin,'admin/user-management?filtered=1');
  await admin.click(`.js-view-user[data-id="${fixture.student}"][data-role="student"]`);
  if(version==='after') await admin.waitForSelector('#userActivity .user-document-button');
  const activity = Boolean(await admin.$('#userActivity .profile-section'));
  assert.equal(activity,version==='after');
  records.push({version,profileHasDocumentsAndActivity:activity});
  await shot(admin,'profile-desktop');
  await admin.setViewport({width:390,height:844});
  await shot(admin,'profile-mobile');
  await admin.setViewport({width:1440,height:1000});
  await admin.evaluate(()=>closeUserViewModal());
  await admin.evaluate(src=>openLightbox(src),receiptUrl);
  if(version==='after') await admin.waitForFunction(()=>document.getElementById('driveedLightboxImage').complete && document.getElementById('driveedLightboxImage').naturalWidth>0);
  else await admin.waitForFunction(()=>document.getElementById('lightboxImage').complete && document.getElementById('lightboxImage').naturalWidth>0);
  await shot(admin,'license-viewer');

  await visit(admin,'admin/enrollments');
  await admin.evaluate(src=>openImageLightbox(src,'GCash Receipt'),receiptUrl);
  await shot(admin,'receipt-viewer');
  const qrForReceipt = await admin.$eval('#driveedQrScanModal',el=>getComputedStyle(el).display!=='none');
  assert.equal(qrForReceipt,version==='before');
  records.push({version,gcashReceiptUsesQrViewer:qrForReceipt});
  await visit(admin,'admin/enrollments');
  await admin.evaluate(()=>openImageLightbox('/missing-gcash-receipt.png','GCash Receipt'));
  if(version==='after') await admin.waitForFunction(()=>!document.getElementById('driveedLightboxError').hidden);
  await shot(admin,'missing-image');

  await visit(admin,'admin/theoretical');
  await admin.click('.claim-notice-trigger');
  const dialog = await admin.$eval('#claimNoticeDialog',el=>{const r=el.getBoundingClientRect();return {x:r.x,y:r.y,width:r.width,height:r.height};});
  records.push({version,pickupDialog:dialog});
  await shot(admin,'pickup-desktop');
  await admin.setViewport({width:390,height:844});
  await shot(admin,'pickup-mobile');
  await admin.setViewport({width:1440,height:1000});

  await visit(admin,'admin/enrollments');
  await admin.setViewport({width:1100,height:850});
  await shot(admin,'enrollment-actions');
  records.push({version,enrollmentActionsWrap:await admin.$eval('.action-buttons',el=>getComputedStyle(el).flexWrap)});
  await admin.setViewport({width:1440,height:1000});
  await admin.evaluate(()=>window.scrollTo(0,document.documentElement.scrollHeight));
  await shot(admin,'enrollment-pagination');
  await context.close();

  const learnerContext = await browser.createBrowserContext();
  const learner = await learnerContext.newPage();
  learner.on('pageerror',e=>errors.push({version,message:e.message}));
  async function loginLearner(role) {
   await visit(learner,'login');
   await learner.type('[name=email]',`qa-nov7-${role}@example.invalid`);
   await learner.type('[name=password]','Nov7-Local-Test-123!');
   await Promise.all([learner.waitForNavigation({waitUntil:'networkidle2'}),learner.click('button[type=submit]')]);
   assert(learner.url().includes('/'+role));
  }
  await loginLearner('student');
  await visit(learner,'student/payments');
  const receipts = await learner.$$eval('[data-image-url]',els=>els.length);
  assert.equal(receipts>0,version==='after');
  records.push({version,studentReceiptButtons:receipts});
  await shot(learner,'payments-desktop');
  await learner.setViewport({width:390,height:844});
  await shot(learner,'payments-mobile');
  await learnerContext.close();

  const guestContext=await browser.createBrowserContext();
  const guest=await guestContext.newPage();
  guest.on('pageerror',e=>errors.push({version,message:e.message}));
  await visit(guest,'login');
  await guest.type('[name=email]','qa-nov7-guest@example.invalid');
  await guest.type('[name=password]','Nov7-Local-Test-123!');
  await Promise.all([guest.waitForNavigation({waitUntil:'networkidle2'}),guest.click('button[type=submit]')]);
  await visit(guest,'guest/enrollment-requests');
  const guestDetails=Boolean(await guest.$('.submitted-payment-details'));
  assert.equal(guestDetails,version==='after');
  records.push({version,guestShowsSubmittedPaymentDetails:guestDetails});
  await shot(guest,'guest-desktop');
  await guest.setViewport({width:390,height:844});
  await shot(guest,'guest-mobile');
  await guestContext.close();
 }
 const before=records.find(r=>r.version==='before' && r.summary)?.summary;
 const after=records.find(r=>r.version==='after' && r.summary)?.summary;
 assert.notDeepEqual(before,after);
 assert.deepEqual(errors,[]);
 await fs.writeFile(new URL('results.json',output),JSON.stringify({baseline:'Git HEAD 6a85c5c, isolated local container on port 8005',viewports:{desktop:[1440,1000],mobile:[390,844],actions:[1100,850]},records,errors},null,2));
 const html=`<!doctype html><html lang="en"><meta charset="utf-8"><title>November 7 findings — before and after</title><style>body{font:16px system-ui;background:#f1f3f5;color:#17212b;margin:0;padding:32px}h1{margin:0 0 12px}header{max-width:1100px;margin-bottom:36px}section{background:white;padding:24px;margin:24px 0;border:1px solid #d5dbe0;border-radius:8px}h2{margin:0 0 8px;font-size:23px}p{line-height:1.5}nav{display:flex;flex-wrap:wrap;gap:12px}nav a{color:#1558ad}.pair{display:grid;grid-template-columns:1fr 1fr;gap:20px}figure{margin:0;min-width:0}figcaption{font-weight:700;margin:12px 0}img{display:block;width:100%;height:auto;border:1px solid #d5dbe0;box-sizing:border-box}.mobile img{max-width:390px;margin:auto}small{color:#526170}@media(max-width:700px){body{padding:12px}.pair{grid-template-columns:1fr}section{padding:12px}}</style><header><h1>November 7 findings: before and after</h1><p>Matched captures from the pre-fix Git revision and the current application, using the same temporary QA records and sample documents. Captured November 8, 2026.</p><small>Before is reconstructed from Git HEAD 6a85c5c in a separate local container. Existing uploads were not altered. Desktop: 1440 × 1000; mobile: 390 × 844; enrollment actions: 1100 × 850.</small><nav>${cases.map(([key,title])=>`<a href="#${key}">${title}</a>`).join('')}</nav></header>${cases.map(([key,title,caption])=>`<section id="${key}" class="${key.includes('mobile')?'mobile':''}"><h2>${title}</h2><p>${caption}</p><div class="pair"><figure><figcaption>Before</figcaption><a href="${key}-before.png"><img src="${key}-before.png" alt="Before: ${title}"></a></figure><figure><figcaption>After</figcaption><a href="${key}-after.png"><img src="${key}-after.png" alt="After: ${title}"></a></figure></div></section>`).join('')}</html>`;
 await fs.writeFile(new URL('index.html',output),html);
 const gallery=await browser.newPage();
 await gallery.setViewport({width:1500,height:1100});
 await gallery.goto(new URL('index.html',output).href,{waitUntil:'load'});
 await gallery.addStyleTag({content:'header{display:none}body{padding:0}section{display:none;margin:0}section[data-capture]{display:block}'});
 for(const key of ['payment-summary','profile-desktop','pickup-desktop','receipt-viewer','payments-mobile','guest-mobile','enrollment-pagination']) {
  await gallery.evaluate(key=>{document.querySelectorAll('section').forEach(section=>section.toggleAttribute('data-capture',section.id===key));window.scrollTo(0,0);},key);
  await (await gallery.$('#'+key)).screenshot({path:fileURLToPath(new URL(`${key}-comparison.png`,output))});
 }
 console.log('PASS: 15 matched before/after screenshot pairs captured; baseline differences confirmed; no JavaScript errors.');
} finally {
 await browser.close();
 if(fixture) console.log(execFileSync('docker',['exec','--user','www-data','driveed_hub_app','php','tests/nov7-browser-fixture.php','cleanup'],{encoding:'utf8'}));
}
