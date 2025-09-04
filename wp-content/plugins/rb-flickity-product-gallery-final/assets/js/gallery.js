(function($){
  'use strict';

  function wrapIndex(i, len){
    if(len <= 0) return 0;
    return ((i % len) + len) % len;
  }

  function initWidget($root){
    if ($root.data('rbpdp-initialized')) return;

    var mainEl = $root.find('.rbpdp-gallery--main')[0];
    if (!mainEl) return;
    var prevEl = $root.find('.rbpdp-gallery--previous')[0];
    var nextEl = $root.find('.rbpdp-gallery--next')[0];

    var baseOpts = {
      selectedAttraction: 0.03,
      friction: 0.35,
      draggable: true,
      cellSelector: '.rbp-slide',
      wrapAround: true,
      setGallerySize: true,
      cellAlign: 'center',
      prevNextButtons: false,
      pageDots: false,
      watchCSS: false,
      imagesLoaded: true,
      percentPosition: false
    };

    function realInit(){
      // MAIN em 0 (ajuste se quiser outro índice inicial)
      var mainOpts = $.extend({}, baseOpts, { initialIndex: 0 });
      var flkMain = new Flickity(mainEl, mainOpts);

      // Total de slides após init do main
      var total = flkMain.cells ? flkMain.cells.length : $root.find('.rbpdp-gallery--main .rbp-slide').length;

      // Índices relativos das laterais
      var idxPrev = wrapIndex(mainOpts.initialIndex - 1, total); // -1
      var idxNext = wrapIndex(mainOpts.initialIndex + 1, total); // +1

      // Cria laterais já no índice relativo
      var flkPrev = prevEl ? new Flickity(prevEl, $.extend({}, baseOpts, { initialIndex: idxPrev })) : null;
      var flkNext = nextEl ? new Flickity(nextEl, $.extend({}, baseOpts, { initialIndex: idxNext })) : null;

      // Boot: seleção INSTANTÂNEA só aqui (para não mostrar 3 iguais no primeiro frame pós-JS)
      if (flkPrev) flkPrev.select(idxPrev, true, true); // wrapped=true, instant=true
      if (flkNext) flkNext.select(idxNext, true, true);

      // Após o boot, sincronize SEM "instant" para animar normalmente
      function syncAdj(index){
        if (flkPrev) flkPrev.select(wrapIndex(index - 1, total), true, false); // anima
        if (flkNext) flkNext.select(wrapIndex(index + 1, total), true, false); // anima
      }

      flkMain.on('ready', function(){ syncAdj(flkMain.selectedIndex); });
      flkMain.on('change', function(index){ syncAdj(index); });

      // SETAS: mudam só o MAIN; laterais seguem via syncAdj (animado)
      $root.off('click.rbpdp').on('click.rbpdp', '.rbp-btn-prev', function(){
        flkMain.previous();
      });
      $root.on('click.rbpdp', '.rbp-btn-next', function(){
        flkMain.next();
      });

      // Libera o paint (Anti-FOUC v3 espera essa classe)
      $root.addClass('rbpdp--ready');

      // Safety de resize/reposition nos primeiros ciclos
      var ticks = 0, timer = setInterval(function(){
        ticks++;
        try{
          if (flkMain.resize) flkMain.resize();
          if (flkMain.reposition) flkMain.reposition();
          if (flkPrev && flkPrev.resize) flkPrev.resize();
          if (flkNext && flkNext.resize) flkNext.resize();
        }catch(e){}
        if (ticks >= 10) clearInterval(timer);
      }, 300);

      $root.data('rbpdp-initialized', true);
    }

    // Inicializa rápido; o anti-FOUC segura o primeiro paint até marcarmos --ready
    setTimeout(realInit, 30);
  }

  function mount(ctx){
    (ctx ? $(ctx) : $(document)).find('.rbpdp').each(function(){
      initWidget($(this));
    });
  }

  // Inicial
  $(document).ready(function(){
    mount();
    // Elementor: monta ao renderizar o widget
    if (window.elementorFrontend && window.elementorFrontend.hooks) {
      elementorFrontend.hooks.addAction(
        'frontend/element_ready/rb_flickity_pdp_gallery_final.default',
        function(scope){ mount(scope); }
      );
      elementorFrontend.hooks.addAction('frontend/element_ready/global', function(scope){ mount(scope); });
    }
  });

  // Reage a inserções dinâmicas (Royal Addons/Elementor)
  try{
    var mo = new MutationObserver(function(muts){
      for (var i=0;i<muts.length;i++){
        var m = muts[i];
        if (m.addedNodes && m.addedNodes.length){
          $(m.addedNodes).each(function(){
            var $n = $(this);
            if ($n.hasClass('rbpdp')) initWidget($n);
            $n.find('.rbpdp').each(function(){ initWidget($(this)); });
          });
        }
      }
    });
    mo.observe(document.body, { childList: true, subtree: true });
  }catch(e){}

})(jQuery);

