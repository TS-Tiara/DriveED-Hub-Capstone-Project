import puppeteer from 'puppeteer-core';
import fs from 'node:fs/promises';
const out=process.env.AUDIT_OUTPUT||'docs/screenshots/nov8-all-roles';
const m=JSON.parse((await fs.readFile('tmp/nov8-audit-manifest.json','utf8')).replace(/^\uFEFF/,''));
const base='http://localhost:8006',root=base+'/'+m.school;
const browser=await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--no-sandbox'],defaultViewport:{width:1440,height:1000}});
const results=[];
async function probe(page,role,name,path,expected){const r=await page.evaluate(async path=>{const r=await fetch(path,{headers:{Accept:'application/json'},redirect:'manual'});return {status:r.status,type:r.headers.get('content-type'),bytes:(await r.arrayBuffer()).byteLength};},path);const pass=Array.isArray(expected)?expected.includes(r.status):expected===r.status;results.push({role,name,path,expected,...r,pass});console.log((pass?'PASS ':'FINDING ')+role+' '+name+' '+r.status);}
try {
 for(const role of ['school-admin','branch-secretary','instructor','student','guest']) {
  const context=await browser.createBrowserContext(),p=await context.newPage();
  await p.goto(root+'/login',{waitUntil:'domcontentloaded'});await p.type('[name=email]',m.roles[role].email);await p.type('[name=password]','Audit-Local-123!');
  await Promise.all([p.waitForNavigation({waitUntil:'domcontentloaded'}),p.click('button[type=submit]')]);
  if(p.url().includes('/login'))throw Error('Login failed for '+role);
  if(role==='branch-secretary') {
   const foreign=m.roles['school-admin'].parameters;
   await probe(p,role,'Foreign-branch enrollment API',root+`/admin/enrollments/api/${foreign.enrollmentRequest}`,403);
   await probe(p,role,'Foreign-branch user activity',root+`/admin/user-management/student/${foreign.student}/activity`,404);
   await probe(p,role,'Foreign-branch payment record',root+`/admin/payments/${foreign.payment}`,403);
   await probe(p,role,'Foreign-branch payment image',root+`/admin/enrollments/${foreign.enrollmentRequest}/view-payment-proof`,403);
  }
  if(role==='school-admin') {
   const a=m.roles[role].parameters;
   await p.goto(root+`/admin/courses/${a.course}/modules/${a.module}`,{waitUntil:'domcontentloaded'});
   const create=await p.$('a[href*="/lessons/create"]');
   if(create){await Promise.all([p.waitForNavigation({waitUntil:'domcontentloaded'}),create.click()]);const code=await p.evaluate(async()=> (await fetch(location.href,{headers:{Accept:'application/json'}})).status);results.push({role,name:'Visible Create Lesson link is usable',status:code,expected:200,pass:code===200});await p.screenshot({path:out+'/screenshots/admin-create-lesson-forbidden.png'});}
  }
  const prefix=['school-admin','branch-secretary'].includes(role)?'admin':role;
  await probe(p,role,'Cross-school portal is denied',base+'/'+m.otherSchool+'/'+prefix+(prefix==='guest'?'/dashboard':''),[0,302,401,403]);
  await context.close();
 }
 const c=await browser.createBrowserContext(),p=await c.newPage();
 await p.goto(root+'/login',{waitUntil:'domcontentloaded'});
 await probe(p,'unauthenticated','Receipt requires login',root+'/guest/receipt?path='+encodeURIComponent(m.receipts.student.path),401);
 await c.close();
}finally{await fs.writeFile(out+'/security-results.json',JSON.stringify(results,null,2));await browser.close();}
