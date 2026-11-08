import puppeteer from 'puppeteer-core';
import fs from 'node:fs/promises';
import assert from 'node:assert/strict';
const phase=process.argv[2];assert(['before','after'].includes(phase));
const out='docs/screenshots/nov8-seven-fixes';await fs.mkdir(out,{recursive:true});
const m=JSON.parse((await fs.readFile('tmp/nov8-audit-manifest.json','utf8')).replace(/^\uFEFF/,''));
assert.equal(m.database,'drivingapp_regression_20261108');
const base='http://localhost:8006',root=base+'/'+m.school;
const browser=await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--no-sandbox'],defaultViewport:{width:1440,height:1000}});
const records=[];const pause=ms=>new Promise(resolve=>setTimeout(resolve,ms));
async function shot(page,key,status,extra={}){await pause(250);await page.screenshot({path:`${out}/${key}-${phase}.png`,fullPage:false});records.push({key,status,url:page.url(),...extra});console.log(`CAPTURED ${phase}: ${key} HTTP ${status}`);}
async function login(role){const c=await browser.createBrowserContext();const p=await c.newPage();await p.goto(root+'/login',{waitUntil:'domcontentloaded'});await p.type('[name=email]',m.roles[role].email);await p.type('[name=password]','Audit-Local-123!');await Promise.all([p.waitForNavigation({waitUntil:'domcontentloaded'}),p.click('button[type=submit]')]);assert(!p.url().includes('/login'));return p;}
try {
 const student=await login('student');
 let response=await student.goto(root+'/guest/receipt?path='+encodeURIComponent(m.receipts.guest.path),{waitUntil:'load'});
 assert.equal(response.status(),phase==='before'?200:403);
 await shot(student,'receipt-ownership',response.status());

 const secretary=await login('branch-secretary');
 response=await secretary.goto(root+`/admin/enrollments/${m.receipts.otherBranch.enrollment}/view-payment-proof`,{waitUntil:'load'});
 assert.equal(response.status(),phase==='before'?200:403);
 await shot(secretary,'branch-receipt',response.status());
 response=await secretary.goto(root+'/admin/settings',{waitUntil:'domcontentloaded'});
 assert.equal(response.status(),phase==='before'?200:403);
 await shot(secretary,'secretary-permissions',response.status());

 const admin=await login('school-admin');const p=m.roles['school-admin'].parameters;
 response=await admin.goto(root+`/admin/enrollments/${p.enrollmentRequest}`,{waitUntil:'domcontentloaded'});
 assert.equal(response.status(),phase==='before'?500:200);
 if(phase==='after')await admin.waitForFunction(()=>document.getElementById('v-modal-content')?.style.visibility==='visible');
 await shot(admin,'enrollment-detail',response.status());
 response=await admin.goto(root+`/admin/courses/${p.course}/modules/${p.module}/lessons/create`,{waitUntil:'domcontentloaded'});
 assert.equal(response.status(),phase==='before'?403:200);
 if(phase==='after')assert((await admin.$eval('form.lms-form',el=>el.action)).includes('/admin/'));
 await shot(admin,'admin-lessons',response.status());
 await admin.goto(root+'/admin/courses',{waitUntil:'domcontentloaded'});
 await admin.evaluate(()=>{
  openCreateModal();
  document.getElementById('courseTitle').value=coursesData[0].title;
  const type=document.getElementById('courseType');type.value=[...type.options].find(o=>o.value)?.value||'';
  document.getElementById('courseCourseType').value='theoretical';
  document.getElementById('courseLicenseType').value='non_professional';
  document.getElementById('courseHoursRequired').value='8';
  document.getElementById('coursePrice').value='100';
  toggleComboCoursesContainer();
 });
 const duplicateResponse=admin.waitForResponse(r=>r.url()===root+'/admin/courses'&&r.request().method()==='POST');
 await admin.evaluate(()=>document.getElementById('courseForm').dispatchEvent(new Event('submit',{bubbles:true,cancelable:true})));
 response=await duplicateResponse;
 assert.equal(response.status(),phase==='before'?500:422);
 await pause(150);await admin.$eval('#courseTitle',el=>el.scrollIntoView({block:'center'}));
 await shot(admin,'duplicate-course',response.status(),{fieldErrors:phase==='after'?(await response.json()).errors:null});

 const instructor=await login('instructor');
 response=await instructor.goto(root+`/instructor/progress/${m.roles.instructor.parameters.progress}/edit`,{waitUntil:'domcontentloaded'});
 assert.equal(response.status(),phase==='before'?500:200);
 await shot(instructor,'progress-edit',response.status());

 const resetContext=await browser.createBrowserContext(),reset=await resetContext.newPage();
 await reset.setExtraHTTPHeaders({'X-Forwarded-For':phase==='before'?'192.0.2.110':'192.0.2.111'});
 await reset.goto(base+'/system-admin/forgot-password',{waitUntil:'domcontentloaded'});
 await reset.type('[name=email]',m.roles['system-admin'].email);
 [response]=await Promise.all([reset.waitForNavigation({waitUntil:'domcontentloaded'}),reset.click('button[type=submit]')]);
 assert.equal(response.status(),phase==='before'?500:200);
 if(phase==='after')assert((await reset.$eval('body',el=>el.innerText)).includes('If that email is registered'));
 await shot(reset,'system-password-reset',response.status());

 await fs.writeFile(`${out}/${phase}-results.json`,JSON.stringify({phase,baseline:phase==='before'?'7d42f0e':undefined,viewport:[1440,1000],records},null,2));
}finally{await browser.close();}
