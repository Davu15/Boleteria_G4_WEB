// El navegador requiere una interacción para habilitar audio. El lector-teclado también la proporciona.
window.Sonido = (() => {
  let context;
  async function activar() {
    try { const Audio = window.AudioContext || window.webkitAudioContext; if (!Audio) return false;
      context ||= new Audio(); await context.resume(); return context.state === 'running';
    } catch { return false; }
  }
  function pitar(veces) {
    if (!context || context.state !== 'running') return false;
    for (let i=0;i<veces;i++) { const osc=context.createOscillator(), gain=context.createGain();
      osc.frequency.value=880; gain.gain.value=0.12; osc.connect(gain); gain.connect(context.destination);
      osc.start(context.currentTime+i*0.25); osc.stop(context.currentTime+i*0.25+0.1); }
    return true;
  }
  return { activar, pitar };
})();
