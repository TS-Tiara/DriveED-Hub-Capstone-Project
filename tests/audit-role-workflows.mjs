import puppeteer from 'puppeteer-core';
import fs from 'node:fs/promises';
const out=process.env.AUDIT_OUTPUT||'docs/screenshots/nov8-all-roles';
const fixed=process.env.EXPECT_FIXED==='1';
const m=JSON.parse((await fs.readFile('tmp/nov8-audit-manifest.json','utf8')).replace(/^\uFEFF/,''));
const base='http://localhost:8006',root=base+'/'+m.school;
const browser=await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--no-sandbox'],defaultViewport:{width:1440,height:1000}});
const results=[],errors=[];
const csrfTokens=new WeakMap();
const record=(name,expected,actual,extra={})=>{const pass=expected===actual;results.push({name,pass,expected,actual,...extra});console.log((pass?'PASS ':'FINDING ')+name+' '+JSON.stringify(actual));};
async function login(email){const c=await browser.createBrowserContext();const p=await c.newPage();p.on('pageerror',e=>errors.push(e.message));await p.goto(root+'/login',{waitUntil:'domcontentloaded'});await p.type('[name=email]',email);await p.type('[name=password]','Audit-Local-123!');await Promise.all([p.waitForNavigation({waitUntil:'domcontentloaded'}),p.click('button[type=submit]')]);csrfTokens.set(p,await p.$eval('meta[name=csrf-token]',el=>el.content));return p;}
async function request(page,path,method='GET',data) {return page.evaluate(async({url,method,data,csrf})=>{
 const headers={Accept:'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]')?.content||document.querySelector('input[name=_token]')?.value||csrf};
 let body;if(data){body=new FormData();for(const [key,value]of Object.entries(data)){if(value&&typeof value==='object')for(const [k,v]of Object.entries(value))body.append(`${key}[${k}]`,v);else if(value!==null)body.append(key,value);}if(method!=='POST'&&method!=='GET'){body.set('_method',method);method='POST';}}
 const r=await fetch(url,{method,headers,body});const text=await r.text();let json;try{json=JSON.parse(text);}catch{}
 return {status:r.status,url:r.url,json,text:text.slice(0,300)};
},{url:root+path,method,data,csrf:csrfTokens.get(page)||''});}
try {
 const secretary=await login(m.roles['branch-secretary'].email);
 await secretary.goto(root+'/admin/settings',{waitUntil:'domcontentloaded'});
 record('Secretary cannot open school-wide settings',403,await secretary.evaluate(async()=> (await fetch(location.href,{headers:{Accept:'application/json'}})).status));
 const saved=fixed?await request(secretary,'/admin/settings','POST',{login_school_name_text:'QA SECRETARY SETTINGS WRITE'}):await secretary.evaluate(async()=>{const form=document.getElementById('settingsForm');if(!form)return {status:null};const data=new FormData(form);data.set('login_school_name_text','QA SECRETARY SETTINGS WRITE');const r=await fetch(form.action,{method:'POST',body:data,headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'}});return {status:r.status,text:(await r.text()).slice(0,300)};});
 await secretary.goto(root+'/admin/settings',{waitUntil:'domcontentloaded'});
 const changed=fixed?saved.status!==403:await secretary.$eval('[name=login_school_name_text]',el=>el.value==='QA SECRETARY SETTINGS WRITE');
 record('Secretary cannot save school-wide settings',false,changed,{response:saved});
 await secretary.screenshot({path:out+'/screenshots/secretary-settings-write.png'});
 const financial=await request(secretary,'/admin/reports');
 record('Secretary cannot view school financial reports',403,financial.status,{response:financial.text});
 await secretary.goto(root+'/admin/reports',{waitUntil:'domcontentloaded'});
 await secretary.screenshot({path:out+'/screenshots/secretary-financial-reports.png'});
 const unauthorizedCourse={title:'QA SECRETARY COURSE '+Date.now(),type:'theoretical',course_type:'theoretical',license_type:'non_professional',hours_required:8,price:50,status:'active'};
 const unauthorized=await request(secretary,'/admin/courses','POST',unauthorizedCourse);
 record('Secretary cannot create courses',403,unauthorized.status,{response:unauthorized.json});
 const courseTester=fixed?await login(m.roles['school-admin'].email):secretary;
 if(fixed)await request(courseTester,'/admin/courses','POST',unauthorizedCourse);
 const duplicate=await request(courseTester,'/admin/courses','POST',unauthorizedCourse);
 record('Duplicate course title returns a validation response',422,duplicate.status,{response:duplicate.json});
 await secretary.goto(root+'/admin/courses',{waitUntil:'domcontentloaded'});
 await secretary.screenshot({path:out+'/screenshots/secretary-created-course.png'});

 const instructor=await login(m.roles.instructor.email);
 const params=m.roles.instructor.parameters;
 if(fixed){
  const adminLessons=`/admin/courses/${params.course}/modules/${params.module}/lessons`;
  const form=await courseTester.goto(root+adminLessons+'/create',{waitUntil:'domcontentloaded'});
  record('Administrator opens lesson create form',200,form.status());
  record('Administrator lesson form posts to admin route',true,await courseTester.$eval('form[enctype]',el=>el.action.includes('/admin/')));
  const lesson=await request(courseTester,adminLessons,'POST',{title:'QA administrator lesson',content:'<p>Admin lesson content</p>',sort_order:101});
  record('Administrator creates lesson',true,lesson.json?.success===true);
  if(lesson.json?.lesson?.id){
   const id=lesson.json.lesson.id;
   record('Administrator opens lesson edit form',200,(await courseTester.goto(root+adminLessons+'/'+id+'/edit',{waitUntil:'domcontentloaded'})).status());
   record('Administrator updates lesson',true,(await request(courseTester,adminLessons+'/'+id,'PUT',{title:'QA administrator updated lesson',content:'<p>Updated</p>',sort_order:101})).json?.success===true);
   record('Administrator deletes lesson via admin module',true,(await request(courseTester,adminLessons+'/'+id,'DELETE',{})).url.includes('/admin/courses/'));
  }
  await courseTester.goto(root+'/admin',{waitUntil:'domcontentloaded'});
  for(let visit=1;visit<=2;visit++){
   await courseTester.evaluate(path=>loadContent(path),root+'/admin/enrollments/'+m.roles['school-admin'].parameters.enrollmentRequest);
   await courseTester.waitForFunction(()=>document.getElementById('unifiedVerificationModal')?.style.display==='flex',{timeout:15000});
   await courseTester.waitForFunction(()=>document.getElementById('v-modal-loading')?.style.display==='none',{timeout:15000});
   record('Enrollment detail opens verification through AJAX '+visit,true,true);
  }
  await instructor.goto(root+`/instructor/progress/${params.progress}/edit`,{waitUntil:'domcontentloaded'});
  const note='QA verified progress update';
  record('Instructor saves progress',true,(await request(instructor,`/instructor/progress/${params.progress}`,'PUT',{completion_percent:50,notes:note})).status===200);
  await instructor.reload({waitUntil:'domcontentloaded'});
  record('Instructor progress change persists',note,await instructor.$eval('[name=notes]',el=>el.value));
 }
 for(const [type,answer,options]of [['multiple_choice','A',{A:'Stop',B:'Go'}],['true_false','True',null],['identification','Stop sign',null],['enumeration','Mirror, Signal',null]]) {
  const label='QA Audit '+type+' '+Date.now();
  const data={question_text:label,question_type:type,correct_answer:answer,options,default_points:2,course_id:params.course};
  const create=await request(instructor,'/instructor/questions','POST',data);
  record('Create '+type+' question',true,create.status===200&&create.json?.success===true,{status:create.status});
  await instructor.goto(root+'/instructor/questions?search='+encodeURIComponent(label),{waitUntil:'domcontentloaded'});
  const href=await instructor.$eval('a.lms-link-edit[href*="/questions/"]',el=>el.getAttribute('href')).catch(()=>null);
  if(!href){record('Locate saved '+type+' question',true,false);continue;}
  const id=href.match(/questions\/(\d+)\/edit/)[1];
  const update=await request(instructor,`/instructor/questions/${id}`,'PUT',{...data,question_text:label+' updated'});
  record('Update '+type+' question',true,update.status===200&&update.json?.success===true,{status:update.status});
  const remove=await request(instructor,`/instructor/questions/${id}`,'DELETE',{});
  record('Delete '+type+' question',true,remove.status===200,{status:remove.status});
 }
 const lessons=`/instructor/courses/${params.course}/modules/${params.module}/lessons`;
 const created=await request(instructor,lessons,'POST',{title:'QA Audit created lesson',content:'<p>Lesson content</p>',sort_order:100});
 record('Instructor creates lesson',true,created.json?.success===true,{status:created.status});
 if(created.json?.lesson?.id){const id=created.json.lesson.id;const updated=await request(instructor,lessons+'/'+id,'PUT',{title:'QA Audit updated lesson',content:'<p>Updated content</p>',sort_order:100});record('Instructor updates lesson',true,updated.json?.success===true,{status:updated.status});}

 const active=m.activeStudent,student=await login(active.email);
 for(const [name,path]of [['lesson list',`/student/courses/${active.course}/modules/${active.module}/lessons`],['lesson detail',`/student/courses/${active.course}/modules/${active.module}/lessons/${active.lesson}`],['assessment',`/student/courses/${active.course}/modules/${active.assessment}/assessment`]]) {
  const r=await student.goto(root+path,{waitUntil:'domcontentloaded'});record('Approved student opens '+name,200,r.status());
  await student.screenshot({path:out+'/screenshots/active-student-'+name.replaceAll(' ','-')+'.png'});
 }
 const complete=await request(student,`/student/courses/${active.course}/modules/${active.module}/lessons/${active.lesson}/complete`,'POST',{});
 record('Approved student completes a lesson',true,complete.status===200&&complete.json?.completed===true,{response:complete.json});
 const other=await request(student,`/admin/user-management/student/${m.otherStudent}/activity`);
 record('Student cannot read admin user documents',401,other.status);

 const publicContext=await browser.createBrowserContext(),publicPage=await publicContext.newPage();
 await publicPage.goto(root+'/register',{waitUntil:'domcontentloaded'});
 const signup=await request(publicPage,'/register','POST',{name:'QA Audit Registration',email:`qa-audit-registration-${Date.now()}@example.invalid`,password:'Audit-Local-123!',password_confirmation:'Audit-Local-123!',contact:'09123456789',accept_privacy:1,accept_terms:1});
 record('Guest registration succeeds with consent and valid details',true,signup.status===200&&(/verify|verification/.test(signup.url)||signup.json?.success===true),{status:signup.status,url:signup.url,response:signup.json||signup.text});
 await publicPage.goto(root+'/forgot-password',{waitUntil:'domcontentloaded'});
 await publicPage.setExtraHTTPHeaders({'X-Forwarded-For':'192.0.2.81'});
 const invalidReset=await request(publicPage,'/forgot-password','POST',{email:'not-an-email'});
 record('Password reset rejects malformed email',422,invalidReset.status);
 const resetContext=await browser.createBrowserContext(),resetPage=await resetContext.newPage();
 await resetPage.goto(base+'/system-admin/forgot-password',{waitUntil:'domcontentloaded'});
 const systemReset=await resetPage.evaluate(async(email)=>{const data=new FormData();data.set('_token',document.querySelector('input[name=_token]').value);data.set('email',email);const r=await fetch('/system-admin/forgot-password',{method:'POST',body:data,headers:{Accept:'application/json','X-Forwarded-For':'192.0.2.82'}});return {status:r.status,body:(await r.text()).slice(0,300)};},m.roles['system-admin'].email);
 record('System-admin password-reset request avoids server errors',true,systemReset.status<500,{response:systemReset});

 record('Workflow browser JavaScript errors',0,errors.length,{errors});
}catch(error){results.push({name:'Workflow harness',pass:false,error:error.stack});console.log(error.stack);}
finally{await fs.writeFile(out+'/workflow-results.json',JSON.stringify(results,null,2));await browser.close();}
