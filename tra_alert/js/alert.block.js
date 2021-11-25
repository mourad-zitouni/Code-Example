(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.alertsBlock = {
    attach: function(context,settings) {
      var block_alert = $(context).find('.block-alerts');
      if(block_alert.length !== 1) {
        return false;
      }

      var storage = window.sessionStorage;
      updateRender();
      block_alert.on('show.bs.collapse hide.bs.collapse' , '.block-alerts__panel', function(event) {
        var active = block_alert.find('button[aria-controls="'+$(this).attr('id')+'"]');
        var label = active.attr('data-toggle-label');
        active.attr('data-toggle-label', active.text());
        active.text(label);
        var header = active.closest('.block-alerts__header');
        if (header.length !== 0) {
          header.toggleClass('active', event.type === 'show');
        }
      }).on('click', 'button[data-target-remove]', function () {
        var id = $(this).attr('data-target-remove');
        updateStorage(id);
        updateRender();
      }).fadeIn();

      showHighlightedAlert();

      function updateStorage(id) {
        var masked_alert = [];
        if (storage.getItem('MaskedAlert')) {
          masked_alert = JSON.parse(storage.getItem('MaskedAlert'));
        }
        masked_alert.push(id);
        storage.setItem('MaskedAlert', JSON.stringify(masked_alert));
      }
      
      function getStorage() {
        var masked_alert = [];
        if (storage.getItem('MaskedAlert')) {
          masked_alert = JSON.parse(storage.getItem('MaskedAlert'));
        }
        return masked_alert;
      }

      function updateRender() {
        var masked_alert = getStorage();
        var splitter = block_alert.find('#panel_splitter');

        $.each( masked_alert, function (index , value) {
          var target = 'panel-alert-'+value;
          var panel = block_alert.find('#'+target);
          var head = block_alert.find('button[aria-controls="'+target+'"]').closest('.block-alerts__header');
          if(panel.length !== 0 && head.length!== 0) {
            panel.remove();
            head.remove();
          }
        });
        
        if (block_alert.find('.block-alerts__panel').length === 0) {
          block_alert.remove();
          return false;
        }

        if (splitter.length === 1) {
          block_alert.find('.block-alerts__panel').each(function (index, item) {
            var panel = $(item);
            var head = block_alert.find('button[aria-controls="'+panel.attr('id')+'"]').closest('.block-alerts__header');
            panel.toggleClass('block-alerts__panel--split' , index >= 2);
            head.toggleClass('block-alerts__header--split' , index >= 2);
            if (index === 1) {
              splitter.insertAfter(panel);
            }
          });
        }
      }

      function showHighlightedAlert() {
        var highlight = block_alert.find('.block-alerts__panel[data-alert-type="enlevement"]').first();
        if (highlight.length === 1) {
          highlight.collapse('toggle');
        }
      }
    },

    detach: function (context, trigger) {

    }
  };
}(jQuery, Drupal));