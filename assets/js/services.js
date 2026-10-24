const url = new URL('../../backend/api.php', import.meta.url);
let state={users:[],slots:[],appointments:[]},me=null,csrf='';
async function request(action,body){const endpoint=new URL(url);endpoint.searchParams.set('action',action);const options={credentials:'same-origin',cache:'no-store'};if(body!==undefined){options.method='POST';options.headers={'Content-Type':'application/json','X-CSRF-Token':csrf};options.body=JSON.stringify(body)}let response;try{response=await fetch(endpoint,options)}catch{throw new Error('Cannot connect to PHP server. Open the project through XAMPP (localhost).')}let result;try{result=await response.json()}catch{throw new Error('PHP did not return JSON. Check Apache, MySQL and the backend configuration.')}if(!response.ok||!result.ok)throw new Error(result.error||'Request failed');return result}
export async function refresh(){const result=await request('bootstrap');state=result.db;me=result.current;csrf=result.csrf;return result}
async function change(action,body){await request(action,body);await refresh()}
export const dbService={all:()=>state};
export const auth={current:()=>me,async login(email,password){const r=await request('login',{email,password});await refresh();return me},async register(data){await request('register',data);await refresh();return me},async logout(){await request('logout',{});me=null;state={users:[],slots:[],appointments:[]}}};
const dt=s=>new Date(`${s.date}T${s.start}:00`).getTime();
export const slots={all:()=>state.slots,remaining:s=>Math.max(0,s.capacity-s.booked),available(){return state.slots.filter(s=>dt(s)>Date.now()&&this.remaining(s)>0).sort((a,b)=>dt(a)-dt(b))},add:d=>change('addSlot',d),remove:id=>change('deleteSlot',{id})};
export const appointments={all:()=>state.appointments,book:(slotId,note)=>change('book',{slotId,note}),cancel:id=>change('cancel',{id}),status:(id,status)=>change('status',{id,status})};
export const users={toggle:id=>change('toggle',{id})};
