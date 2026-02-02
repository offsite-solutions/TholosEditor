/**
 * Bootstrap Tabs Dynamic - Bootstrap 5 Version
 *
 * A jQuery plugin for dynamically adding, removing, and editing Bootstrap 5 tabs.
 * Refactored from the original bootstrap-tabs-dynamic by kaspernj.
 *
 * Bootstrap 5 Tab Structure:
 * <ul class="nav nav-tabs" id="myTab" role="tablist">
 *   <li class="nav-item" role="presentation">
 *     <button class="nav-link active" id="home-tab" data-bs-toggle="tab"
 *             data-bs-target="#home-tab-pane" type="button" role="tab"
 *             aria-controls="home-tab-pane" aria-selected="true">Home</button>
 *   </li>
 * </ul>
 * <div class="tab-content" id="myTabContent">
 *   <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel"
 *        aria-labelledby="home-tab" tabindex="0">...</div>
 * </div>
 *
 * Usage:
 *   const tabs = $("ul.nav-tabs");
 *   tabs.addBSTab("my-tab", "My Tab Title", "<p>Content here</p>");
 *
 *   const tab = tabs.getBSTabByID("my-tab");
 *   tab.renameBSTab("New Title");
 *   tab.removeBSTab();
 */

(function($) {
  'use strict';

  /**
   * Find the associated tab-content container for a nav-tabs element
   * @param {jQuery} tabsContainer - The ul.nav-tabs element
   * @returns {jQuery} The associated .tab-content element
   */
  function findTabContent(tabsContainer) {
    // Try to find by ID pattern (e.g., myTab -> myTabContent)
    const tabsId = tabsContainer.attr('id');
    if (tabsId) {
      const contentById = $('#' + tabsId + 'Content');
      if (contentById.length) return contentById;
    }

    // Try to find the next sibling .tab-content
    let tabContent = tabsContainer.next('.tab-content');
    if (tabContent.length) return tabContent;

    // Try to find within the same parent
    tabContent = tabsContainer.siblings('.tab-content').first();
    if (tabContent.length) return tabContent;

    // Try to find closest common ancestor then find .tab-content
    tabContent = tabsContainer.parent().find('.tab-content').first();
    if (tabContent.length) return tabContent;

    // Last resort: find any .tab-content in the document
    return $('.tab-content').first();
  }

  /**
   * Generate a safe ID from a string
   * @param {string} str - Input string
   * @returns {string} Safe ID string
   */
  function generateSafeId(str) {
    return str
      .toLowerCase()
      .replace(/[^a-z0-9]/g, '-')
      .replace(/-+/g, '-')
      .replace(/^-|-$/g, '');
  }

  /**
   * Add a new Bootstrap 5 tab
   * @param {string} id - Unique ID for the tab
   * @param {string} title - Tab title text
   * @param {string|jQuery} content - Tab pane content (HTML string or jQuery element)
   * @param {Object} options - Optional settings
   * @param {boolean} options.active - Whether to make this tab active (default: false)
   * @param {boolean} options.disabled - Whether the tab should be disabled (default: false)
   * @param {boolean} options.fade - Whether to use fade animation (default: true)
   * @param {Function} options.onShow - Callback when tab is shown
   * @param {Function} options.onHide - Callback when tab is hidden
   */
  $.fn.addBSTab = function(id, title, content, options) {
    const settings = $.extend({
      active: false,
      disabled: false,
      fade: true,
      onShow: null,
      onHide: null
    }, options);

    return this.each(function() {
      const $tabsContainer = $(this);
      const $tabContent = findTabContent($tabsContainer);

      const tabId = id + '-tab';
      const paneId = id + '-tab-pane';

      // Check if tab already exists
      if ($('#' + tabId).length > 0) {
        console.warn('Bootstrap Tabs Dynamic: Tab with ID "' + id + '" already exists.');
        return;
      }

      // Determine if this should be active (first tab or explicitly set)
      const isFirstTab = $tabsContainer.find('.nav-item').length === 0;
      const shouldBeActive = settings.active || isFirstTab;

      // Create the nav-item with button
      const $navItem = $('<li>', {
        class: 'nav-item',
        role: 'presentation'
      });

      const $button = $('<button>', {
        class: 'nav-link' + (shouldBeActive ? ' active' : ''),
        id: tabId,
        type: 'button',
        role: 'tab',
        'data-bs-toggle': 'tab',
        'data-bs-target': '#' + paneId,
        'aria-controls': paneId,
        'aria-selected': shouldBeActive ? 'true' : 'false'
      }).text(title);

      if (settings.disabled) {
        $button.prop('disabled', true);
      }

      $navItem.append($button);
      $tabsContainer.append($navItem);

      // Create the tab pane
      const fadeClass = settings.fade ? ' fade' : '';
      const activeClass = shouldBeActive ? ' show active' : '';

      const $tabPane = $('<div>', {
        class: 'tab-pane' + fadeClass + activeClass,
        id: paneId,
        role: 'tabpanel',
        'aria-labelledby': tabId,
        tabindex: '0'
      });

      // Handle content - can be string or jQuery element
      if (typeof content === 'string') {
        $tabPane.html(content);
      } else if (content instanceof $ || content instanceof HTMLElement) {
        $tabPane.append($(content).show());
      }

      $tabContent.append($tabPane);

      // If this tab should be active, deactivate others
      if (shouldBeActive && !isFirstTab) {
        $tabsContainer.find('.nav-link').not($button).removeClass('active').attr('aria-selected', 'false');
        $tabContent.find('.tab-pane').not($tabPane).removeClass('show active');
      }

      // Set up event callbacks if provided
      if (settings.onShow && typeof settings.onShow === 'function') {
        $button.on('shown.bs.tab', settings.onShow);
      }

      if (settings.onHide && typeof settings.onHide === 'function') {
        $button.on('hidden.bs.tab', settings.onHide);
      }

      // Initialize Bootstrap Tab if Bootstrap JS is available
      if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
        new bootstrap.Tab($button[0]);
      }
    });
  };

  /**
   * Get a tab nav-item element by its ID
   * @param {string} id - The tab ID (without -tab suffix)
   * @returns {jQuery} The nav-item li element
   */
  $.fn.getBSTabByID = function(id) {
    const tabId = id + '-tab';
    const $button = this.find('#' + tabId);

    if ($button.length === 0) {
      // Try without -tab suffix in case full ID was passed
      const $buttonAlt = this.find('#' + id);
      if ($buttonAlt.length > 0) {
        return $buttonAlt.closest('.nav-item');
      }
      return $();
    }

    return $button.closest('.nav-item');
  };

  /**
   * Remove a Bootstrap 5 tab
   * Called on a nav-item element
   */
  $.fn.removeBSTab = function() {
    return this.each(function() {
      const $navItem = $(this);
      const $button = $navItem.find('.nav-link');
      const targetSelector = $button.attr('data-bs-target');
      const $tabPane = $(targetSelector);
      const wasActive = $button.hasClass('active');

      // If this was the active tab, activate another tab
      if (wasActive) {
        const $tabsContainer = $navItem.closest('.nav-tabs');
        const $siblings = $tabsContainer.find('.nav-item').not($navItem);

        if ($siblings.length > 0) {
          // Prefer next sibling, then previous
          let $nextTab = $navItem.next('.nav-item');
          if ($nextTab.length === 0) {
            $nextTab = $navItem.prev('.nav-item');
          }

          if ($nextTab.length > 0) {
            const $nextButton = $nextTab.find('.nav-link:not([disabled])');
            if ($nextButton.length > 0) {
              // Use Bootstrap's Tab API if available
              if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                const tab = bootstrap.Tab.getOrCreateInstance($nextButton[0]);
                tab.show();
              } else {
                // Manual activation
                $nextButton.addClass('active').attr('aria-selected', 'true');
                const nextPaneSelector = $nextButton.attr('data-bs-target');
                $(nextPaneSelector).addClass('show active');
              }
            }
          }
        }
      }

      // Dispose Bootstrap Tab instance if exists
      if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
        const tabInstance = bootstrap.Tab.getInstance($button[0]);
        if (tabInstance) {
          tabInstance.dispose();
        }
      }

      // Remove elements
      $tabPane.remove();
      $navItem.remove();
    });
  };

  /**
   * Rename a Bootstrap 5 tab
   * Called on a nav-item element
   * @param {string} newTitle - The new title text
   */
  $.fn.renameBSTab = function(newTitle) {
    return this.each(function() {
      const $navItem = $(this);
      const $button = $navItem.find('.nav-link');
      $button.text(newTitle);
    });
  };

  /**
   * Get the current tab from any element within a tab pane
   * @returns {jQuery} The nav-item li element for the current tab
   */
  $.fn.currentBSTab = function() {
    const $tabPane = this.closest('.tab-pane');
    if ($tabPane.length === 0) return $();

    const paneId = $tabPane.attr('id');
    const $button = $('[data-bs-target="#' + paneId + '"]');

    return $button.closest('.nav-item');
  };

  /**
   * Get the current tab ID from any element within a tab pane
   * @returns {string|null} The tab ID (without -tab suffix) or null
   */
  $.fn.currentBSTabID = function() {
    const $tabPane = this.closest('.tab-pane');
    if ($tabPane.length === 0) return null;

    const paneId = $tabPane.attr('id');
    // Remove -tab-pane suffix to get base ID
    return paneId.replace(/-tab-pane$/, '');
  };

  /**
   * Show/activate a specific tab
   * Called on a nav-item element
   */
  $.fn.showBSTab = function() {
    return this.each(function() {
      const $navItem = $(this);
      const $button = $navItem.find('.nav-link');

      if ($button.prop('disabled')) {
        console.warn('Bootstrap Tabs Dynamic: Cannot show disabled tab.');
        return;
      }

      if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
        const tab = bootstrap.Tab.getOrCreateInstance($button[0]);
        tab.show();
      } else {
        // Manual activation without Bootstrap JS
        const $tabsContainer = $navItem.closest('.nav-tabs');
        const $tabContent = findTabContent($tabsContainer);

        // Deactivate all tabs
        $tabsContainer.find('.nav-link').removeClass('active').attr('aria-selected', 'false');
        $tabContent.find('.tab-pane').removeClass('show active');

        // Activate this tab
        $button.addClass('active').attr('aria-selected', 'true');
        const targetSelector = $button.attr('data-bs-target');
        $(targetSelector).addClass('show active');
      }
    });
  };

  /**
   * Disable a tab
   * Called on a nav-item element
   */
  $.fn.disableBSTab = function() {
    return this.each(function() {
      const $navItem = $(this);
      const $button = $navItem.find('.nav-link');
      $button.prop('disabled', true);
    });
  };

  /**
   * Enable a tab
   * Called on a nav-item element
   */
  $.fn.enableBSTab = function() {
    return this.each(function() {
      const $navItem = $(this);
      const $button = $navItem.find('.nav-link');
      $button.prop('disabled', false);
    });
  };

  /**
   * Get tab content/pane element
   * Called on a nav-item element
   * @returns {jQuery} The tab-pane element
   */
  $.fn.getBSTabContent = function() {
    const $button = this.find('.nav-link');
    const targetSelector = $button.attr('data-bs-target');
    return $(targetSelector);
  };

  /**
   * Set tab content
   * Called on a nav-item element
   * @param {string|jQuery} content - New content for the tab pane
   */
  $.fn.setBSTabContent = function(content) {
    return this.each(function() {
      const $navItem = $(this);
      const $tabPane = $navItem.getBSTabContent();

      if (typeof content === 'string') {
        $tabPane.html(content);
      } else if (content instanceof $ || content instanceof HTMLElement) {
        $tabPane.empty().append($(content).show());
      }
    });
  };

  /**
   * Check if tab is active
   * Called on a nav-item element
   * @returns {boolean}
   */
  $.fn.isBSTabActive = function() {
    return this.find('.nav-link').hasClass('active');
  };

  /**
   * Get all tabs
   * Called on ul.nav-tabs element
   * @returns {jQuery} Collection of all nav-item elements
   */
  $.fn.getAllBSTabs = function() {
    return this.find('.nav-item');
  };

  /**
   * Get active tab
   * Called on ul.nav-tabs element
   * @returns {jQuery} The active nav-item element
   */
  $.fn.getActiveBSTab = function() {
    return this.find('.nav-link.active').closest('.nav-item');
  };

  /**
   * Remove all tabs
   * Called on ul.nav-tabs element
   */
  $.fn.removeAllBSTabs = function() {
    return this.each(function() {
      const $tabsContainer = $(this);
      const $tabContent = findTabContent($tabsContainer);

      // Dispose all Bootstrap Tab instances
      if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
        $tabsContainer.find('.nav-link').each(function() {
          const tabInstance = bootstrap.Tab.getInstance(this);
          if (tabInstance) {
            tabInstance.dispose();
          }
        });
      }

      $tabsContainer.find('.nav-item').remove();
      $tabContent.find('.tab-pane').remove();
    });
  };

})(jQuery);