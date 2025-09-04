(function($){
  'use strict';

  // NÃO adiciona .rbpdp--ready aqui; quem faz é o gallery.js

  function tryMount(ctx){
    if (window.rbpdpMount) {
      try { window.rbpdpMount(ctx || document); } catch(e){}
    }
  }

  $(function(){
    // Front: tenta montar cedo (o CSS segura o paint até --ready)
    tryMount(document);

    // Elementor Editor: monta a cada render de widget para evitar preview “cru”
    if (window.elementorFrontend && window.elementorFrontend.hooks) {
      elementorFrontend.hooks.addAction('frontend/element_ready/global', function(scope){
        tryMount(scope);
      });
      elementorFrontend.hooks.addAction('frontend/element_ready/rb_flickity_pdp_gallery_final.default', function(scope){
        tryMount(scope);
      });
    }

    // Fallback: se por qualquer motivo o editor segurar muito, tente de novo em curto intervalo
    var tries = 0, iv = setInterval(function(){
      tries++; tryMount(document);
      if (tries > 10) clearInterval(iv);
    }, 400);
  });
})(jQuery);

