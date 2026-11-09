import puppeteer from 'puppeteer-core';
import fs from 'node:fs/promises';
import assert from 'node:assert/strict';
const out='docs/screenshots/nov8-document-findings';
const m=JSON.parse((await fs.readFile('tmp/nov8-document-manifest.json','utf8')).replace(/^\uFEFF/,''));
assert.equal(m.database,'drivingapp_regression_20261108');
const root='http://localhost:8006',school=root+'/'+m.school,checks=[],errors=[];
const browser=await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--no-sandbox'],defaultViewport:{width:1440,height:1000}});
const check=(ok,name,detail)=>{checks.push({name,pass:!!ok,detail});assert(ok,name+': '+JSON.stringify(detail));console.log('PASS '+name);};
const pause=ms=>new Promise(r=>setTimeout(r,ms));
async function login(role){const c=await browser.createBrowserContext(),p=await c.newPage();p.on('pageerror',e=>errors.push({url:p.url(),message:e.message}));await p.goto(role==='system-admin'?root+'/system-admin/login':school+'/login');await p.type('[name=email]',m.roles[role].email);await p.type('[name=password]','Audit-Local-123!');await Promise.all([p.waitForNavigation({waitUntil:'networkidle2'}),p.click('button[type=submit]')]);assert(!p.url().includes('/login'));return p;}
async function go(p,path){const r=await p.goto(path.startsWith('http')?path:school+path,{waitUntil:'networkidle2'});check(r.status()===200,'Page loads '+path,r.status());}
async function shot(p,key,selector){if(selector)await p.$eval(selector,e=>e.scrollIntoView({block:'center'}));await pause(200);await p.screenshot({path:`${out}/${key}-after.png`});}
async function json(p,path,body,method='POST'){return p.evaluate(async({path,body,method})=>{const r=await fetch(path,{method,headers:{Accept:'application/json','Content-Type':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify(body)});return {status:r.status,data:await r.json()};},{path,body,method});}
try{
 const student=await login('student'),admin=await login('school-admin'),instructor=await login('instructor'),browse=await login('browse-student');
 await go(browse,'/student/my-course');
 const summary=await browse.$eval('.available-courses',e=>e.innerText);
 check(summary.includes('15 hours')&&summary.includes('2,301.00')&&summary.includes('2,600.00'),'Package summary uses actual hours and prices',summary);
 await shot(browse,'course-summary','.available-courses');
 await go(student,'/student');
 check(await student.$$eval('[role=progressbar]',els=>els.length>0&&els.every(e=>e.getAttribute('aria-valuenow')==='0'&&parseFloat(e.style.width)===0)),'Zero progress renders with no colored fill');
 check(!(await student.$eval('body',e=>e.innerText)).includes('Active Enrollments'),'Redundant enrollment count is hidden');
 check((await student.$eval('.dashboard-cards',e=>e.innerText)).includes('0/15'),'Learning progress uses enrollment training hours instead of zero base hours');
 await shot(student,'progress-enrollment','.dashboard-cards');
 await go(student,'/student/courses');await shot(student,'course-packages');
 await go(admin,'/admin/settings');
 await admin.evaluate(()=>{document.getElementById('badge_approved_bg').value='#d1fae5';document.getElementById('badge_approved_text').value='#065f46';document.getElementById('badge_approved_bold').checked=true;updatePreview();});
 const saved=await admin.evaluate(async()=>{const f=document.getElementById('settingsForm'),r=await fetch(f.action,{method:'POST',body:new FormData(f),headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'}});return {status:r.status,text:(await r.text()).slice(0,500)};});
 check(saved.status===200,'School badge settings save',saved);
 await go(admin,'/admin/settings');
 check(await admin.$eval('#badge_approved_text',e=>e.value)==='#065f46'&&await admin.$eval('#badge_approved_bold',e=>e.checked),'Text color and bold persist after reload');
 await shot(admin,'status-settings','#section-badges');
 await go(student,'/student/payments');
 const badge=await student.$eval('.badge-approved',e=>{const s=getComputedStyle(e);return [s.backgroundColor,s.color,s.fontWeight];});
 check(JSON.stringify(badge)===JSON.stringify(['rgb(209, 250, 229)','rgb(6, 95, 70)','700']),'Payment status uses school badge colors and weight',badge);
 await shot(student,'payment-status');
 await go(admin,'/admin');
 check(await admin.$eval('.main-content',e=>getComputedStyle(e).backgroundColor)==='rgba(0, 0, 0, 0)','Content no longer masks school background');
 check(await admin.$eval('body',e=>getComputedStyle(e).backgroundColor)==='rgb(232, 240, 251)','Configured solid background is visible');
 await shot(admin,'background');
 await go(admin,'/admin/settings');
 const imageSaved=await admin.evaluate(async()=>{const f=document.getElementById('settingsForm'),data=new FormData(f),canvas=document.createElement('canvas');canvas.width=2;canvas.height=2;const ctx=canvas.getContext('2d');ctx.fillStyle='#dce8fa';ctx.fillRect(0,0,2,2);const blob=await new Promise(r=>canvas.toBlob(r,'image/png'));data.set('background_type','image');data.set('background_image',new File([blob],'qa-background.png',{type:'image/png'}));const r=await fetch(f.action,{method:'POST',body:data,headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'}});return {status:r.status,text:(await r.text()).slice(0,300)};});
 check(imageSaved.status===200,'School background image uploads successfully',imageSaved);
 await go(admin,'/admin');
 const imageBackground=await admin.evaluate(()=>{const s=getComputedStyle(document.body,'::before');return {image:s.backgroundImage,opacity:s.opacity};});
 check(imageBackground.image.includes('/storage/backgrounds/')&&imageBackground.opacity==='1','Uploaded image applies to the page background',imageBackground);
 const loadedImage=await admin.evaluate(async()=>{const value=getComputedStyle(document.body,'::before').backgroundImage;const url=value.slice(5,-2);return new Promise(resolve=>{const img=new Image();img.onload=()=>resolve({loaded:true,width:img.naturalWidth});img.onerror=()=>resolve({loaded:false,url});img.src=url;});});
 check(loadedImage.loaded,'Uploaded background URL loads as an actual image',loadedImage);
 await shot(admin,'background-image');
 await go(admin,'/admin/settings');
 await admin.evaluate(async()=>{const f=document.getElementById('settingsForm'),data=new FormData(f);data.set('background_type','color');await fetch(f.action,{method:'POST',body:data,headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'}});});
 await go(admin,'/admin/schedules');
 await admin.evaluate(course=>{openCreateModal();const s=document.getElementById('createCourseSelect');s.value=course;s.dispatchEvent(new Event('change'));document.getElementById('toggleTimeRanges').checked=true;toggleTimeRanges(true);},String(m.findings.course));
 check(await admin.$$eval('#timeRangesRows select',e=>e.length)===4,'Batch creation starts with two dropdown pairs');
 await admin.select('[name="time_ranges[0][start_time]"]','08:00');await admin.select('[name="time_ranges[0][end_time]"]','09:00');await admin.select('[name="time_ranges[1][start_time]"]','13:00');await admin.select('[name="time_ranges[1][end_time]"]','14:00');
 const date=new Date(Date.now()+12*86400000).toISOString().slice(0,10);await admin.$eval('#createScheduleDate',(e,date)=>e.value=date,date);
 await shot(admin,'schedule-creation','#timeRangesGroup');
 const creation=admin.waitForResponse(r=>r.url().endsWith('/admin/schedules/create')&&r.request().method()==='POST');await admin.click('#submitCreateBtn');const created=await creation;check(created.status()===201,'Same-day dropdown batch submits successfully',created.status());await pause(800);
 await go(admin,'/admin/schedules');
 await admin.evaluate(()=>{openCreateModal();const s=document.getElementById('createCourseSelect');s.value=[...s.options].find(o=>o.dataset.type==='practical').value;s.dispatchEvent(new Event('change'));});
 check(!(await admin.$eval('#createMaxInstructors',e=>e.disabled)),'PDC instructor capacity remains editable with no assignment');
 await admin.$eval('#createMaxInstructors',e=>{e.value=4;e.scrollIntoView({block:'center'});});await shot(admin,'pdc-capacity');
 await admin.evaluate(()=>{closeCreateModal();openCreateModal();const s=document.getElementById('createCourseSelect');s.value=[...s.options].find(o=>o.dataset.type==='theoretical').value;s.dispatchEvent(new Event('change'));document.getElementById('toggleTimeRanges').checked=true;toggleTimeRanges(true);document.getElementById('toggleBatchScheduling').checked=true;toggleBatchMode(true);});
 check(!(await admin.$eval('#toggleTimeRanges',e=>e.checked))&&(await admin.$eval('#timeRangesGroup',e=>e.hidden)),'Sequential scheduling turns off same-day ranges');
 check(await admin.$$eval('#timeRangesRows select',els=>els.every(e=>e.disabled)),'Inactive same-day ranges are excluded from sequential submissions');
 await go(admin,'/admin/schedules');
 const detail=await admin.$('.additional-instructors summary');check(!!detail,'Additional assigned instructors have an interactive disclosure');if(detail){await detail.evaluate(e=>{const group=e.closest('.agenda-day-group');if(group?.classList.contains('collapsed'))group.querySelector('.agenda-header').click();});await detail.click();check(await admin.$eval('.additional-instructors',e=>e.open),'More instructors expands names');await shot(admin,'schedule-instructors','.additional-instructors');}
 await go(instructor,'/instructor/my-schedule');
 await instructor.evaluate(id=>{switchMainView('available');const btn=[...document.querySelectorAll('.btn-select')].find(b=>b.getAttribute('onclick')?.includes('('+id+','));selectSlot(id,btn);},m.findings.slots[0]);
 await instructor.waitForSelector('#batchSchedulePicker[open]');check(await instructor.$$eval('#batchScheduleOptions input',e=>e.length)===3,'Instructor can choose all three batch dates');await instructor.evaluate(()=>setBatchSelection('all'));await shot(instructor,'instructor-batch');
 const selection=instructor.waitForResponse(r=>r.url().endsWith('/timeslots/batch/select'));await instructor.click('#batchSchedulePicker [value=continue]');const selected=await selection;check(selected.status()===200,'Instructor selects whole batch in one request',selected.status());await pause(900);
 await go(student,'/student/schedule');
 await student.evaluate(({id,course,enrollment,instructor})=>{switchMainView('available-schedules');openBookingModal(id,course,'QA Package Driving Course',instructor,'QA Instructor','2099-01-01','09:00','10:00',enrollment);},{id:m.findings.slots[0],course:m.findings.course,enrollment:m.findings.enrollment,instructor:m.findings.instructor});
 await student.waitForSelector('#batchSchedulePicker[open]');await student.evaluate(()=>setBatchSelection('all'));await shot(student,'student-batch');await student.click('#batchSchedulePicker [value=continue]');
 await student.waitForSelector('#bookingModal',{visible:true});const reservation=student.waitForResponse(r=>r.url().endsWith('/bookings/batch'));await student.click('#bookingForm button[type=submit]');const reserved=await reservation;check(reserved.status()===201,'Student reserves whole batch from the picker',reserved.status());await pause(1100);
 await go(instructor,'/instructor/sessions/create');
 check(await instructor.$eval('[name=session_grade]',e=>e.required),'Manual session form requires grade');
 await instructor.select('[name=attendance_status]','absent');check(!(await instructor.$eval('[name=session_grade]',e=>e.required)),'Absent session can be recorded without grade');await instructor.select('[name=attendance_status]','attended');await shot(instructor,'session-grade','[name=session_grade]');
 await go(instructor,'/instructor/sessions');check((await instructor.$eval('body',e=>e.innerText)).includes('Awaiting verification'),'Session list shows admin verification state');await shot(instructor,'session-verification','.session-table-wrapper,table');
 const system=await login('system-admin');await go(system,root+'/system-admin/users?search=nov8-findings');
 await system.evaluate(id=>{const btn=[...document.querySelectorAll('[data-action="delete-user"]')].find(b=>Number(b.dataset.id)===id&&b.dataset.type==='student');if(!btn)throw Error('QA account not in current user page');btn.click();},m.findings.student);
 check(!(await system.$eval('#deleteConfirmation',e=>e.checkValidity())),'Typed confirmation prevents empty account removal');await shot(system,'user-removal');
 // Mobile checks exercise the same controls without submitting new mutations.
 for(const [page,path] of [[student,'/student'],[student,'/student/payments'],[admin,'/admin/settings'],[admin,'/admin/schedules'],[instructor,'/instructor/sessions']]){await page.setViewport({width:390,height:844});await go(page,path);check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),'Mobile has no horizontal page overflow '+path);await shot(page,'mobile-'+path.split('/').filter(Boolean).join('-'));}
 check(errors.length===0,'No browser JavaScript exceptions',errors);
 await system.type('#deleteConfirmation','delete QA Package Learner');
 check(await system.$eval('#deleteConfirmation',e=>e.checkValidity()),'Exact typed confirmation permits account removal');
 await Promise.all([system.waitForNavigation({waitUntil:'networkidle2'}),system.click('#deleteUserForm button[type=submit]')]);
 check((await system.$eval('body',e=>e.innerText)).includes('Historical records have been preserved'),'Confirmed removal reports archival and preserved history');
 await student.goto(school+'/student',{waitUntil:'networkidle2'});
 check(student.url().replace(/\/$/,'')===school && !!(await student.$('[name=email]')),'Archived account loses its existing authenticated session',student.url());
}finally{await fs.writeFile(out+'/focused-browser-results.json',JSON.stringify({checks,errors},null,2));await browser.close();}
