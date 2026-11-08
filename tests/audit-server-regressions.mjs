import {spawn} from 'node:child_process';
import fs from 'node:fs/promises';
const output=process.env.AUDIT_OUTPUT||'docs/screenshots/nov8-all-roles';
await fs.mkdir(output+'/server-logs',{recursive:true});
const tests=[
 'verify-bug-report.php','verify-recheck-edge-cases.php','verify-oct31-findings.php','verify-nov1-experience.php','verify-nov7-findings.php',
 'verify-license-expiration-reminders.php','verify-session-features.php','verify-session-report-export.php','verify-tdc-completion.php','verify-bundle-transitions.php',
 'verify-receipt-serving.php','verify-image-lightbox-zoom.php','verify-table-responsiveness.php','verify-session-edit-fix.php',
 'verify-enrollment-reupload-modals.php','verify-enrollment-and-media.php','verify-all-capstone-items.php',
];
const results=[];
if(process.env.EXPECT_FIXED==='1')tests.push('verify-seven-fixes.php');
for(const file of tests) {
 let log='';const start=Date.now();
 const code=await new Promise((resolve,reject)=>{const child=spawn('docker',['exec','--user','www-data','driveed_hub_nov8_audit','php','tests/'+file],{windowsHide:true});child.stdout.on('data',d=>log+=d);child.stderr.on('data',d=>log+=d);child.on('error',reject);child.on('close',resolve);});
 const markers=log.split(/\r?\n/).filter(line=>/^\s*(?:\[FAIL\]|FAIL[:!]|ERROR[:!]|(?:[A-Za-z_\\]+Exception)\s*$|(?:[A-Za-z_\\]*Error)\s*$)/.test(line));
 const pass=code===0&&markers.length===0&&!/Total Failed:\s*[1-9]/.test(log);
 await fs.writeFile(output+'/server-logs/'+file.replace('.php','.log'),log);
 results.push({file,pass,code,seconds:Math.round((Date.now()-start)/1000),markers});
 console.log((pass?'PASS ':'FAIL ')+file+(markers.length?' '+markers.join(' | '):''));
 if(!pass) console.log(log.slice(-1000));
}
await fs.writeFile(output+'/server-results.json',JSON.stringify(results,null,2));
console.log(`${results.filter(r=>r.pass).length}/${results.length} server suites passed.`);
