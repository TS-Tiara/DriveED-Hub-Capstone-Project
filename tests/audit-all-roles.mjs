import puppeteer from 'puppeteer-core';
import fs from 'node:fs/promises';
import {fileURLToPath} from 'node:url';
const output=process.env.AUDIT_OUTPUT||'docs/screenshots/nov8-all-roles';
await fs.mkdir(output+'/screenshots',{recursive:true});
const manifest=JSON.parse((await fs.readFile('tmp/nov8-audit-manifest.json','utf8')).replace(/^\uFEFF/,''));
const routes=JSON.parse((await fs.readFile('tmp/nov8-routes.json','utf8')).replace(/^\uFEFF/,''));
const base='http://localhost:8006';
const browser=await puppeteer.launch({executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe',headless:true,args:['--no-sandbox'],defaultViewport:{width:1440,height:1000}});
const results=[], boundaries=[], skipped=[], roleErrors=[];
const pause=ms=>new Promise(resolve=>setTimeout(resolve,ms));
const filename=text=>text.replace(/[^a-z0-9_.-]+/gi,'-');
async function snapshot(page,name){const path=output+'/screenshots/'+filename(name)+'.png';await page.screenshot({path,fullPage:false});return path;}
async function fetchResult(page,url,ajax=false) {
 return page.evaluate(async({url,ajax})=>{
  const start=performance.now();const response=await fetch(url,{headers:ajax?{'X-Requested-With':'XMLHttpRequest','Accept':'text/html'}:{}});
  const data=new Uint8Array(await response.arrayBuffer());
  const type=response.headers.get('content-type')||'';
  return {status:response.status,url:response.url,type,bytes:data.length,magic:String.fromCharCode(...data.slice(0,5)),milliseconds:Math.round(performance.now()-start),excerpt:type.includes('text')||type.includes('json')?new TextDecoder().decode(data.slice(0,180)):''};
 },{url,ajax});
}
async function audit(role) {
 const config=manifest.roles[role], prefix=role==='system-admin'?'system-admin':`{school}/${['school-admin','branch-secretary'].includes(role)?'admin':role}`;
 const context=await browser.createBrowserContext();const page=await context.newPage();
 page.setDefaultTimeout(12000);page.setDefaultNavigationTimeout(18000);
 let current='login';const errors=[];
 page.on('pageerror',e=>errors.push({route:current,message:e.message}));
 page.on('response',r=>{if(r.url().startsWith(base)&&r.status()>=500)errors.push({route:current,message:`HTTP ${r.status()} ${r.url()}`});});
 const login=async()=>{
  await page.goto(base+(role==='system-admin'?'/system-admin/login':`/${manifest.school}/login`),{waitUntil:'domcontentloaded'});
  await page.type('[name=email]',config.email);await page.type('[name=password]','Audit-Local-123!');
  await Promise.all([page.waitForNavigation({waitUntil:'domcontentloaded'}),page.click('button[type=submit]')]);
  if(page.url().includes('/login'))throw Error('Login did not reach dashboard: '+page.url());
 };
 try {
  await login();
  await snapshot(page,role+'-dashboard-desktop');
  results.push({role,route:'login',status:200,url:page.url()});
  const inventory=routes.filter(r=>r.method.includes('GET')&&(r.uri===prefix||r.uri.startsWith(prefix+'/'))&&!/\/login$|\/forgot-password$|\/reset-password|\{any/.test(r.uri));
  if(role!=='system-admin')inventory.push(...routes.filter(r=>r.method.includes('GET')&&r.name==='schools.notifications.index'));
  for(const route of inventory) {
   let params={...config.parameters};
   if(route.uri.includes('assessment'))params.module=manifest.assessment;
   if(role==='branch-secretary'&&route.uri.includes('/user-management/'))params.id=params.student;
   const missing=[];
   const uri=route.uri.replace(/\{([^}]+)\}/g,(_,key)=>{if(params[key]===null||params[key]===undefined){missing.push(key);return 'missing';}return encodeURIComponent(params[key]);});
   if(missing.length){skipped.push({role,route:route.name,uri:route.uri,reason:'No matching fixture for '+missing.join(', ')});continue;}
   const url=base+'/'+uri;current=route.name||route.uri;
   const entry={role,route:current,path:uri};const errorStart=errors.length;
   try {
    const download=/exports?\/|\/session-report\/|\/view-license$|\/view-credential$|\/gcash-qr\/|\/vehicle-image\//.test(uri)||/\.export|\.excel|\.pdf$/.test(route.name||'');
    if(download){entry.download=await fetchResult(page,url);entry.status=entry.download.status;}
    else {
     const response=await page.goto(url,{waitUntil:'domcontentloaded'});await pause(150);
     entry.status=response?.status();entry.url=page.url();entry.title=await page.title();
     if(entry.status>=500)entry.excerpt=(await page.$eval('body',el=>el.innerText)).slice(0,500);
     if(entry.status===200){
      entry.ajax=await fetchResult(page,url,true);
      if(!route.uri.slice(prefix.length).includes('{') && !/statistics|notifications/.test(uri)) {
       if(/dashboard|\/admin$|\/system-admin$/.test(uri))entry.screenshot=await snapshot(page,role+'-'+current);
       await page.setViewport({width:390,height:844});await pause(60);
       entry.mobile=await page.evaluate(()=>({width:innerWidth,documentWidth:document.documentElement.scrollWidth,overflow:document.documentElement.scrollWidth>innerWidth+2,offenders:[...document.querySelectorAll('body *')].filter(e=>{const r=e.getBoundingClientRect();return r.width>0&&r.right>innerWidth+4&&getComputedStyle(e).position!=='fixed';}).slice(0,5).map(e=>({tag:e.tagName,id:e.id,class:e.className?.toString().slice(0,100)}))}));
       if(entry.mobile.overflow)entry.mobile.screenshot=await snapshot(page,role+'-'+current+'-mobile-overflow');
       await page.setViewport({width:1440,height:1000});
       const buttons=await page.$$('button[onclick]');
       for(const button of buttons){
        const action=await button.evaluate(el=>el.getAttribute('onclick'));
        const box=await button.boundingBox();
        if(!box||!/^\s*(?:window\.)?open\w*Modal\(/.test(action||''))continue;
        await button.click();await pause(80);
        entry.dialog=await page.evaluate(()=>{const dialog=[...document.querySelectorAll('[role=dialog],dialog[open],.modal,.modal-overlay')].find(e=>{const r=e.getBoundingClientRect();const s=getComputedStyle(e);return r.width>0&&r.height>0&&s.display!=='none'&&s.visibility!=='hidden'&&s.opacity!=='0';});return dialog?{id:dialog.id,visible:true}:null;});
        break;
       }
      }
     }
    }
    entry.errors=errors.slice(errorStart);
    entry.expectedDenied=role==='branch-secretary'&&route.middleware.includes('school.admin.only');
    if(entry.status>=500||entry.errors.length)entry.screenshot=await snapshot(page,role+'-'+current+'-failure');
   }catch(error){entry.error=error.message;entry.errors=errors.slice(errorStart);try{entry.screenshot=await snapshot(page,role+'-'+current+'-failure');}catch{}}
   results.push(entry);console.log(`${role}: ${current} => ${entry.status??entry.error}${entry.ajax?.status>=400?' AJAX '+entry.ajax.status:''}${entry.mobile?.overflow?' MOBILE OVERFLOW':''}${entry.errors?.length?' ERRORS '+entry.errors.length:''}`);
  }

  const probes=[];
  if(role!=='system-admin') {
   probes.push(['system-admin boundary','/system-admin/users',true]);
   if(['student','guest','instructor'].includes(role))probes.push(['school-admin boundary',`/${manifest.school}/admin/payments`,true]);
   if(role==='branch-secretary')probes.push(['admin-management boundary',`/${manifest.school}/admin/admin-management`,true]);
   if(role==='student'||role==='guest') {
    const foreign=manifest.receipts[role==='student'?'guest':'student'];
    if(foreign.payment)probes.push(['another learner receipt',`/${manifest.school}/guest/receipt?path=${encodeURIComponent(foreign.path)}`,true]);
   }
   probes.push(['path traversal',`/${manifest.school}/guest/receipt?path=..%2F..%2F.env`,true]);
  }
  for(const [name,path,mustDeny]of probes){
   const result=await page.evaluate(async path=>{const r=await fetch(path,{headers:{Accept:'application/json'},redirect:'manual'});return {status:r.status,type:r.type,contentType:r.headers.get('content-type'),bytes:(await r.arrayBuffer()).byteLength};},path);
   boundaries.push({role,name,path,mustDeny,...result,pass:result.status===0||[302,401,403,404].includes(result.status)});
   console.log(`BOUNDARY ${role}: ${name} => ${result.status}`);
  }
  roleErrors.push({role,errors});
 }catch(error){results.push({role,route:current,error:error.message});console.log('ROLE ERROR '+role+' '+error.message);}
 finally{await context.close();}
}
try {
 for(const role of ['system-admin','school-admin','branch-secretary','instructor','student','guest'])await audit(role);
}finally {
 await fs.writeFile(output+'/browser-results.json',JSON.stringify({environment:base,database:manifest.database,results,boundaries,skipped,roleErrors},null,2));
 await browser.close();
 const failures=results.filter(r=>r.error||r.status>=500||r.ajax?.status>=500||r.errors?.length);
 console.log(`RESULT: ${results.length} route/login checks; ${failures.length} error cases; ${boundaries.filter(r=>!r.pass).length} failed access-boundary probes; ${skipped.length} untested parameterized routes.`);
}
