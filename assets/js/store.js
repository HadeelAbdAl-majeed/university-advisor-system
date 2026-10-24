import {KEY,initialData} from './data.js';
export function getDB(){try{const value=localStorage.getItem(KEY);if(value){const data=JSON.parse(value);if(Array.isArray(data.users)&&Array.isArray(data.slots)&&Array.isArray(data.appointments))return data}}catch(e){console.warn('Demo data reset needed',e)}const db=initialData();saveDB(db);return db}
export function saveDB(db){localStorage.setItem(KEY,JSON.stringify(db))}
export function nextId(items){return Math.max(0,...items.map(x=>Number(x.id)||0))+1}
export function resetDB(){localStorage.removeItem(KEY);sessionStorage.removeItem('uas_session');getDB()}
