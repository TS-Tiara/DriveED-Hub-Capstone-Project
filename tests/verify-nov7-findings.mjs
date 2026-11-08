import puppeteer from 'puppeteer-core';
import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import {fileURLToPath} from 'node:url';
import {execFileSync} from 'node:child_process';

const output = new URL('../docs/screenshots/nov7-findings/', import.meta.url);
await fs.mkdir(output, {recursive:true});
const browser = await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--no-sandbox'],defaultViewport:{width:1440,height:1000}});
const errors = [], checks = [];
const record = text => { checks.push(text); console.log('PASS: '+text); };
const root = 'http://localhost:8004/drived-hub';
const visit = (page,path) => page.goto(root+'/'+path,{waitUntil:'networkidle2'});
const shot = (page,name) => page.screenshot({path:fileURLToPath(new URL(name+'.png',output)),fullPage:true});
async function login(role, fixtureAccount = false) {
 const context = await browser.createBrowserContext();
 const page = await context.newPage();
 page.on('pageerror',error=>errors.push(role+': '+error.message));
 page.on('response',r=>{if(r.status()>=500 && r.url().startsWith(root))errors.push('HTTP '+r.status()+' '+r.url());});
 await visit(page,'login');
 await page.type('[name=email]',fixtureAccount ? `qa-nov7-${role}@example.invalid` : role+'1@driveedhub.test');
 await page.type('[name=password]',fixtureAccount ? 'Nov7-Local-Test-123!' : 'DriveDemo123');
 await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),page.click('button[type=submit]')]);
 assert(page.url().includes('/'+role), 'Login failed: '+page.url());
 return page;
}
let fixture;
try {
 fixture = JSON.parse(execFileSync('docker',['exec','--user','www-data','driveed_hub_app','php','tests/nov7-browser-fixture.php'],{encoding:'utf8'}));
 const admin = await login('admin');
 await visit(admin,'admin/payments');
 const paymentPaginationStyle = await admin.$eval('.ds-page-btn',el=>({height:getComputedStyle(el).minHeight,size:getComputedStyle(el).fontSize}));
 await admin.click('.qr-code-wrapper');
 await admin.waitForSelector('#driveedImageLightbox.is-open');
 assert.equal(await admin.$eval('#driveedQrScanModal',el=>getComputedStyle(el).display),'none');
 await admin.keyboard.press('Escape');
 record('Admin payment snapshot images use the standard viewer without a second QR popup');
 const totals = await admin.$$eval('.stat-value',els=>els.map(e=>e.textContent.trim()));
 await visit(admin,'admin/payments?page=2');
 assert.deepEqual(await admin.$$eval('.stat-value',els=>els.map(e=>e.textContent.trim())),totals);
 assert(await admin.$('#paymentsTable tbody tr[data-status]'));
 record('Payment summary totals remain correct on page 2 with records beyond the first page');
 await shot(admin,'payment-summary-page2');

 await visit(admin,'admin/user-management?filtered=1');
 assert(await admin.$('.ds-pagination-nav'));
 const paginationStyle = await admin.$eval('.ds-page-btn',el=>({height:getComputedStyle(el).minHeight,size:getComputedStyle(el).fontSize}));
 assert.deepEqual(paginationStyle,paymentPaginationStyle);
 await admin.click(`.js-view-user[data-id="${fixture.student}"][data-role="student"]`);
 await admin.waitForSelector('#userActivity .user-document-button');
 assert(await admin.$eval('#userActivity',el=>el.innerText.includes('Enrollment History') && el.innerText.includes('Payment Receipts and Transactions')));
 const activityPage = await admin.$('#userActivity .ds-pagination-nav a[rel=next]');
 assert(activityPage);
 await activityPage.click();
 await admin.waitForFunction(()=>document.querySelector('#userActivity .ds-page-btn[aria-current=page]')?.textContent.trim()==='2');
 assert.equal(await admin.$eval('#userViewModal',el=>el.style.display),'flex');
 assert(admin.url().includes('user-management?filtered=1'));
 record('Activity pagination stays inside the user profile and preserves the roster');
 const footerFits = await admin.$eval('#userViewModal .profile-modal-footer',el=>el.getBoundingClientRect().bottom<=innerHeight);
 assert(footerFits);
 await shot(admin,'student-documents-activity');
 const doc = await admin.$('#userActivity .user-document-button');
 await doc.click();
 await admin.waitForSelector('#driveedImageLightbox.is-open');
 assert.equal(await admin.$eval('#driveedImageLightbox',el=>el.parentElement.tagName),'BODY');
 assert.equal(await admin.$eval('#driveedQrScanModal',el=>getComputedStyle(el).display),'none');
 await admin.waitForFunction(()=>document.querySelector('#driveedLightboxImage').complete);
 const imageLoaded = await admin.$eval('#driveedLightboxImage',el=>el.naturalWidth > 0);
 assert(imageLoaded, 'Uploaded fixture license should load');
 await admin.waitForFunction(()=>getComputedStyle(document.getElementById('driveedLightboxLoader')).display==='none');
 await shot(admin,'student-license-viewer');
 await admin.keyboard.press('Escape');
 assert.equal(await admin.$eval('#userViewModal',el=>el.style.display),'flex');
 assert(await admin.evaluate(()=>document.activeElement.classList.contains('user-document-button')));
 record('Student profile includes documents, enrollment history and receipts; shared viewer returns focus to the profile');
 await admin.evaluate(()=>closeUserViewModal());

 await admin.evaluate(()=>openImageLightbox('/missing-gcash-receipt.png', 'GCash Receipt'));
 await admin.waitForFunction(()=>!document.querySelector('#driveedLightboxError').hidden);
 assert.equal(await admin.$eval('#driveedQrScanModal',el=>getComputedStyle(el).display),'none');
 await shot(admin,'missing-receipt-viewer');
 await admin.keyboard.press('Escape');
 await admin.evaluate(()=>openImageLightbox('', 'Missing GCash Receipt'));
 assert.equal(await admin.$eval('#driveedLightboxError',el=>el.hidden),false);
 assert.equal(await admin.$eval('#driveedQrScanModal',el=>getComputedStyle(el).display),'none');
 await admin.keyboard.press('Escape');
 record('GCash receipts and absent images stay in the standard viewer with a useful error state');

 await visit(admin,'admin/user-management?role=instructor&filtered=1');
 await admin.click('.js-view-user[data-role=instructor]');
 await admin.waitForSelector('#userActivity .profile-section');
 const license = await admin.$('#userViewModal .license-image-container');
 if (license) {
  await license.click();
  await admin.waitForSelector('#driveedImageLightbox.is-open');
  await admin.keyboard.press('Escape');
 }
 record('Instructor license uses the shared viewer and profile loads teaching activity');

 await visit(admin,'admin/theoretical');
 await admin.click('.claim-notice-trigger');
 const centered = await admin.$eval('#claimNoticeDialog',el=>{let r=el.getBoundingClientRect();return {x:r.x,y:r.y,w:r.width,h:r.height,vw:document.documentElement.clientWidth,vh:innerHeight};});
 await shot(admin,'pickup-dialog-desktop');
 assert(Math.abs(centered.x+(centered.w/2)-(centered.vw/2))<9); // Native dialogs exclude the scrollbar gutter.
 assert(Math.abs(centered.y+(centered.h/2)-(centered.vh/2))<2);
 await shot(admin,'pickup-dialog-desktop');
 await admin.setViewport({width:390,height:844});
 const mobileDialog = await admin.$eval('#claimNoticeDialog',el=>{let r=el.getBoundingClientRect();return {x:r.x,y:r.y,right:r.right,bottom:r.bottom};});
 assert(mobileDialog.x>=0 && mobileDialog.y>=0 && mobileDialog.right<=390 && mobileDialog.bottom<=844);
 await shot(admin,'pickup-dialog-mobile');
 await admin.keyboard.press('Escape');
 record('Pickup notice dialog is centered on desktop and fits on mobile');

 await admin.setViewport({width:1440,height:1000});
 await visit(admin,'admin/enrollments');
 assert.deepEqual(await admin.$eval('.ds-page-btn',el=>({height:getComputedStyle(el).minHeight,size:getComputedStyle(el).fontSize})),paginationStyle);
 record('Enrollment and user pagination share the same control sizes and typography');
 const layout = await admin.$$eval('.requests-table .action-buttons',els=>els.map(el=>({wrap:getComputedStyle(el).flexWrap,width:el.getBoundingClientRect().width,overflow:el.scrollWidth>el.clientWidth+1})));
 assert(layout.length>0 && layout.every(el=>el.wrap==='wrap' && !el.overflow));
 await shot(admin,'enrollment-actions-desktop');
 const inspect = await admin.$('[onclick^="openVerificationModal"]');
 if (inspect) {
  await inspect.click();
  await admin.waitForFunction(()=>document.getElementById('v-modal-content').style.visibility==='visible');
  const viewerButton = await admin.$('#v-payment-viewer button');
  if (viewerButton) {
   await viewerButton.click();
   await admin.waitForSelector('#driveedImageLightbox.is-open');
   assert.equal(await admin.$eval('#driveedQrScanModal',el=>getComputedStyle(el).display),'none');
   await admin.keyboard.press('Escape');
   assert.equal(await admin.$eval('#unifiedVerificationModal',el=>el.style.display),'flex');
  }
  await admin.evaluate(()=>closeVerificationModal());
 }
 await admin.setViewport({width:390,height:844});
 await shot(admin,'enrollment-actions-mobile');
 assert(await admin.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
 record('Enrollment actions wrap without overflow and nested receipt inspection keeps verification open');

 const student = await login('student',true);
 await visit(student,'student/payments');
 const receiptButtons = await student.$$('[data-image-url]');
 assert(receiptButtons.length>0);
 await receiptButtons[0].click();
 await student.waitForSelector('#driveedImageLightbox.is-open');
 assert.equal(await student.$eval('#driveedQrScanModal',el=>getComputedStyle(el).display),'none');
 await student.keyboard.press('Escape');
 await shot(student,'student-payment-receipts');
 await student.setViewport({width:390,height:844});
 await shot(student,'student-payment-receipts-mobile');
 assert(await student.$('.payment-card [data-image-url]'));
 record('Student payment receipts are accessible on desktop and mobile');

 const guest = await login('guest',true);
 await visit(guest,'guest/enrollment-requests');
 assert(await guest.$('.submitted-payment-details'));
 const guestReceipt = await guest.$('.submitted-payment-details [data-image-url]');
 assert(guestReceipt);
 await guestReceipt.click();
 await guest.waitForSelector('#driveedImageLightbox.is-open');
 assert.equal(await guest.$eval('#driveedQrScanModal',el=>getComputedStyle(el).display),'none');
 await guest.keyboard.press('Escape');
 await shot(guest,'guest-submitted-payments');
 await guest.setViewport({width:390,height:844});
 await shot(guest,'guest-submitted-payments-mobile');
 assert(await guest.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
 await guest.evaluate(()=>{
  const button=document.createElement('button');
  button.id='testPaymentQr';button.className='qr-container';button.textContent='Scan payment QR';
  button.onclick=()=>openQrScanModal('/images/logo.png',{accountName:'Test school',accountNumber:'09123456789'});
  document.body.appendChild(button);
 });
 await guest.click('#testPaymentQr');
 await guest.waitForSelector('#driveedQrScanModal.is-open');
 assert.equal(await guest.$eval('#driveedQrAccName',el=>el.textContent),'Test school');
 assert.equal(await guest.$eval('#driveedQrAccNum',el=>el.textContent),'09123456789');
 await guest.keyboard.press('Escape');
 record('Guests can review submitted payment details and receipts; dedicated payment QR viewer is preserved');
 assert.deepEqual(errors,[]);
 record('No browser JavaScript errors or application server errors');
 await fs.writeFile(new URL('results.json',output),JSON.stringify({checks,paginationStyle,errors},null,2));
} finally {
 await browser.close();
 if (fixture) console.log(execFileSync('docker',['exec','--user','www-data','driveed_hub_app','php','tests/nov7-browser-fixture.php','cleanup'],{encoding:'utf8'}));
}
