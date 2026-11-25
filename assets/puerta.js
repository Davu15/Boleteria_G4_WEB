(() => {
  const form=document.querySelector('[data-gate]'); if(!form) return;
  const input=form.querySelector('[data-code]'), result=document.querySelector('[data-result]');
  let readerTimer, resetTimer, processing=false; const pending=[];
  const sound=document.querySelector('[data-sound]');
  sound.addEventListener('click',async()=>{ sound.textContent=await Sonido.activar()?'Sonido activado':'Audio no disponible en este navegador'; });
  input.addEventListener('keydown',()=>{ Sonido.activar(); });
  function esperar() { result.className='card access-result'; result.dataset.tipo='0';
    result.replaceChildren(); const symbol=document.createElement('div'); symbol.className='result-symbol'; symbol.textContent='◎';
    const title=document.createElement('h2'); title.textContent='ESPERANDO LECTURA'; const text=document.createElement('p'); text.textContent='Acerque el boleto al lector'; result.append(symbol,title,text); }
  function mostrar(data) { clearTimeout(resetTimer); result.className='card access-result '+data.status; result.dataset.tipo=String(data.tipo||2);
    const symbol=document.createElement('div'); symbol.className='result-symbol'; symbol.textContent=data.tipo===1?'✓':'×';
    const title=document.createElement('h2'); title.textContent=data.title; const text=document.createElement('p'); text.textContent=data.message;
    const code=document.createElement('strong'); code.className='mono'; code.textContent=data.code||'';
    result.replaceChildren(symbol,title,text,code); Sonido.pitar(data.tipo===1?1:data.tipo===3?3:2); resetTimer=setTimeout(esperar,4000); }
  async function procesar() { if(processing) return; processing=true;
    while(pending.length) { const code=pending.shift(); const body=new URLSearchParams(new FormData(form)); body.set('codigo',code);
      try { const response=await fetch(form.getAttribute('action')||location.href,{method:'POST',body,headers:{Accept:'application/json'}});
        const data=await response.json(); mostrar(data);
      } catch { mostrar({tipo:2,status:'error',title:'Error de conexión',message:'No se pudo confirmar el resultado. Comprueba la conexión antes de continuar.'}); }
    }
    processing=false; input.focus();
  }
  form.addEventListener('submit',event=>{ event.preventDefault(); clearTimeout(readerTimer); const code=input.value.trim(); input.value='';
    if(code) { pending.push(code); procesar(); } });
  input.addEventListener('input',()=>{ clearTimeout(readerTimer); if(input.value.trim()) readerTimer=setTimeout(()=>form.requestSubmit(),300); });
  window.addEventListener('focus',()=>input.focus());
  if(Number(result.dataset.tipo)) resetTimer=setTimeout(esperar,4000);
})();
