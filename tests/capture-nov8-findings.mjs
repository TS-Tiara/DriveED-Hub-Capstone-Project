import puppeteer from 'puppeteer-core';
import fs from 'node:fs/promises';
import assert from 'node:assert/strict';
const phase=process.argv[2]; assert(['before','after'].includes(phase));
const out='docs/screenshots/nov8-document-findings'; await fs.mkdir(out,{recursive:true});
const manifest=JSON.parse((await fs.readFile('tmp/nov8-audit-manifest.json','utf8')).replace(/^\uFEFF/,''));
const browser=await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--no-sandbox'],defaultViewport:{width:1440,height:1000}});
const root='http://localhost:8006',school=root+'/'+manifest.school,results=[];
async function login(role){const context=await browser.createBrowserContext(),page=await context.newPage(); await page.goto(role==='system-admin'?root+'/system-admin/login':school+'/login'); await page.type('[name=email]',manifest.roles[role].email);await page.type('[name=password]','Audit-Local-123!');await Promise.all([page.waitForNavigation({waitUntil:'networkidle2'}),page.click('button[type=submit]')]);assert(!page.url().includes('/login'));return page;}
async function capture(page,key,url,prepare){const response=await page.goto(url,{waitUntil:'networkidle2'});assert.equal(response.status(),200,key);if(prepare)await prepare(page);await new Promise(r=>setTimeout(r,300));await page.screenshot({path:`${out}/${key}-${phase}.png`});results.push({key,status:response.status(),url});console.log('Captured '+key+' '+phase);}
try{
 const student=await login('student');
 await capture(student,'course-summary',school+'/student',p=>p.evaluate(()=>{[...document.querySelectorAll('h2,h3')].find(e=>e.textContent.includes('Available Courses'))?.scrollIntoView({block:'center'});}));
 await capture(student,'progress-enrollment',school+'/student');
 await capture(student,'course-packages',school+'/student/courses');
 await capture(student,'payment-status',school+'/student/payments');
 await capture(student,'student-batch',school+'/student/schedule',p=>p.evaluate(()=>{if(typeof switchMainView==='function')switchMainView('available-schedules');}));
 const admin=await login('school-admin');
 await capture(admin,'schedule-creation',school+'/admin/schedules',p=>p.evaluate(()=>openCreateModal()));
 await capture(admin,'status-settings',school+'/admin/settings',p=>p.evaluate(()=>{const s=document.getElementById('section-badges');s?.scrollIntoView({block:'center'});}));
 await capture(admin,'background',school+'/admin');
 await capture(admin,'schedule-instructors',school+'/admin/schedules',p=>p.evaluate(()=>{document.querySelector('.table-responsive')?.scrollIntoView({block:'center'});}));
 const instructor=await login('instructor');
 await capture(instructor,'instructor-batch',school+'/instructor/my-schedule',p=>p.evaluate(()=>{if(typeof switchMainView==='function')switchMainView('available-slots');}));
 await capture(instructor,'session-verification',school+'/instructor/sessions');
 await capture(instructor,'session-grade',school+'/instructor/sessions/create');
 const system=await login('system-admin');
 await capture(system,'user-removal',root+'/system-admin/users',p=>p.evaluate(()=>{const b=document.querySelector('[data-action="delete-user"]'); if(b)b.click();else{const b=[...document.querySelectorAll('[data-name]')].find(e=>e.getAttribute('onclick')?.toLowerCase().includes('delete'));b?.click();}}));
 await fs.writeFile(`${out}/${phase}-results.json`,JSON.stringify(results,null,2));
}finally{await browser.close();}
