/*!
 * KWF Script: kwe.js
 * Based on DOMcraft
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-12-09
 * @version 3.0
 */

/* JSLint: First asume we have the KWF Framework */
/*global elem, getTarget, returnFalse, addEvent, removeEvent, addSubmitEvent, 
   previousNode, nextNode, firstChildElement, lastChildElement, hasClass, 
   addClass, removeClass, replaceClass, giveOpacity, parseJSON, var_dump, 
   toDOMnode, Ajax, Boxing, Kwf, KWFEventTarget, content_request, boxing_request */

/**
 * Contains functions for opening dialogs
 * @class KDialog
 * @constructor
 * @param {String} title KDialog title
 * @param {HTMLElement/String} content Containing HTML of this dialog as string or element
 * @param {Number} width Dialog width
 * @param {Number} height Dialog height
 * @param {Function} click_listener A listener for the click event
 */
var KDialog = function(title, content, width, height, click_listener)
  {
  /**
   * Reference to this object
   * @property self
   * @type Object
   * @private
   */
  var self = this, 

  /**
   * Reference to the dialog element
   * @property dialog
   * @type HTMLDivElement
   * @private
   */
  dialog = document.createElement('div'), 

  /**
   * Reference to the dialog content element
   * @property content_div
   * @type HTMLDivElement
   * @private
   */
  content_div = null;

  /**
   * Handles click events on dialog
   * @method click
   * @private
   * @param {Event} e The event object
   */
  function click(e)
    {
    var targ = getTarget(e);

    if (hasClass(targ, 'kdialog-close'))
      {
      self.close();
      }

    if (typeof click_listener === 'function')
      {
      click_listener(e, targ);
      }
    }

  /**
   * Sets the dialog content
   * @method setContent
   * @public
   * @param {HTMLElement/String} content Containing HTML of this dialog as string or element
   */
  self.setContent = function(content)
    {
    if (typeof content === 'string')
      {
      content_div.innerHTML += content;
      }
    else
      {
      content_div.innerHTML = '';
      content_div.appendChild(content);
      }
    };

  /**
   * Closes this dialog
   * @method close
   * @public
   */
  self.close = function()
    {
    dialog.parentNode.removeChild(dialog);
    };

  /* Initiation */

  dialog.className = 'kdialog';
  dialog.style.width = width + 'px';
  dialog.style.height = height + 'px';
  dialog.style.margin = '-' + (height / 2) + 'px 0 0 -' + (width / 2) + 'px';
  dialog.innerHTML = '<div class="kdialog-bar"><span class="kdialog-title">' + title + '</span><span class="kdialog-close">Stäng</span></div>';
  content_div = dialog.appendChild(toDOMnode('<div class="kdialog-content"></div>'));
  self.setContent(content);
  document.body.appendChild(dialog);

  addEvent(dialog, 'click', click);
  };

var Kwe = (function(window, document, elem, content_request, boxing_request, Boxing)
  {
  /**
   * Listener for Ajax beforecallback
   * @method beforeAjax
   * @private
   */
  function beforeAjax()
    {
    var ajax_loader = elem('ajax_loader');

    if (!ajax_loader)
      {
      ajax_loader = document.createElement('div');
      ajax_loader.id = 'ajax_loader';
      ajax_loader.appendChild(document.createTextNode('Laddar...'));
      elem('header').appendChild(ajax_loader);
      }

    ajax_loader.style.display = 'block';
    }

  /**
   * Listener for Ajax aftercallback
   * @method afterAjax
   * @private
   */
  function afterAjax()
    {
    elem('ajax_loader').style.display = 'none';
    }

  /**
   * Starts a new ContentRequest
   * @method contentLink
   * @private
   * @param {Event} e The event object
   * @param {HTMLElement} link The link that fired the event
   */
  function contentLink(e, link)
    {
    content_request.load(e, link.getAttribute('href'));
    }

  /**
   * Starts a new BoxingRequest. Define the dimensions of Boxing with CSS class "dimWIDTHxHEIGHT"
   * @method boxingLink
   * @private
   * @param {Event} e The event object
   * @param {HTMLElement} link The link that fired the event
   */
  function boxingLink(e, link)
    {
    var width = 400, height = 300, 
      cls = link.className, match;

    if (cls.indexOf('dim') > -1)
      {
      match = new RegExp('dim(\\d+)x(\\d+)').exec(cls);
      width = match[1];
      height = match[2];
      }

    boxing_request.load(e, link.getAttribute('href'), width, height);
    }

  /**
   * Listener for afterload events. Removes content in AJAX responses if the 
   * target has the CSS class "no-content".
   * @method noRespContent
   * @private
   * @param {Event} e The request object
   */
  function noRespContent(e)
    {
    if (hasClass(e.target, 'no-content'))
      {
      this.response.page.content = '';
      }
    }

  /**
   * Contains functions for extending Domiwyg and inject Domiwyg into KWE
   * @class Wysiwyg
   * @private
   */
  var Wysiwyg = (function(domiwyg)
    {
    return {
      /**
       * Extends Domiwyg with KWE specific buttons
       * @method init
       * @public
       */
      init: function()
        {
        if (typeof domiwyg !== 'undefined')
          {
          domiwyg.lang.create_kwe_link = 'Infoga länk till en annan sida på denna webbplats';
          domiwyg.lang.insert_kwe_image = 'Infoga bild som finns uppladdat till KWE';
          domiwyg.lang.insert_kwe_flash = 'Infoga Flashobjekt';
          domiwyg.lang.insert_kwe_youtube= 'Infoga film från YouTube';
          domiwyg.tool_btns.splice(6, 0, ['KWElink', 'create_kwe_link'], 
            ['KWEimage', 'insert_kwe_image'], 
            ['KWEflash', 'insert_kwe_flash'], 
            ['KWEyoutube', 'insert_kwe_youtube']);

          domiwyg.area.prototype.cmdKWElink = Wysiwyg.KWElink;
          domiwyg.area.prototype.cmdKWEimage = Wysiwyg.KWEimage;
          domiwyg.area.prototype.cmdKWEflash = Wysiwyg.KWEflash;
          domiwyg.area.prototype.cmdKWEyoutube = Wysiwyg.KWEyoutube;
          }
        },

      /**
       * Shows the "Select KWE Link" dialog. Let's the user select an internal 
       * page to link to
       * @method KWElink
       * @public
       */
      KWElink: function()
        {
        var self = this, lang = domiwyg.lang, 
          element = self.getSelectedAreaElement(), 
          node_name = null, 
          input = document.createElement('input'), 
          body = document.body;

        if (element)
          {
          node_name = element.nodeName.toLowerCase();
          self.storeCursor();

          if (node_name)
            {
            if (node_name !== 'a')
              {
              element = null;
              }

            Browser.openPageBrowser(function(page)
              {
              input.id = 'dw_link_url';
              input.value = Kwf.MODR_SITE + page;
              body.appendChild(input);
              self.createLink(element);
              body.removeChild(input);
              });
            }
          }
        },

      /**
       * Shows the "Select KWE Image" dialog. Let's the user select an uploaded
       * image to embed
       * @method KWEimage
       * @public
       */
      KWEimage: function()
        {
        var self = this, 
          input = document.createElement('input'), 
          body = document.body;

        if (self.getSelectedAreaElement())
          {
          self.storeCursor();

          Browser.openImageBrowser(function(image)
            {
            input.id = 'dw_img_url';
            input.value = Kwf.FULLPATH_SITE + image;
            body.appendChild(input);
            self.insertImage();
            body.removeChild(input);
            });
          }
        },

      /**
       * Shows the "Select Flash object" dialog
       * @method KWEflash
       * @public
       */
      KWEflash: function()
        {
        var self = this, 
          dialog, 
          element = self.getSelectedAreaElement();

        if (element)
          {
          self.storeCursor();

          dialog = new KDialog(domiwyg.lang.insert_kwe_flash, 
            '<form><p><label for="kwe_flash_url">URL</label><input type="text" '
            + 'id="kwe_flash_url"  /></p><p><label for="kwe_flash_width">Bredd</label>'
            + '<input type="text" id="kwe_flash_width" /></p><p>'
            + '<label for="kwe_flash_height">Höjd</label><input type="text" '
            + 'id="kwe_flash_height" /></p><p><button type="button" id="kwe_insert_flash">'
            + 'OK</button></p></form>', 300, 200, function(e, targ)
            {
            if (targ.id === 'kwe_insert_flash')
              {
              self.restoreCursor();
              element = self.getFirstContainer(element);
              element.container.insertBefore(toDOMnode('<img src="' + Kwf.FULLPATH_SITE
                + '/images/admin/flashlayer.png" width="' + elem('kwe_flash_width').value
                + '" height="' + elem('kwe_flash_height').value
                + '" data-url="' + elem('kwe_flash_url').value
                + '" class="kwe-flash-layer" />'), element.reference);
              dialog.close();
              }
            });

          elem('kwe_flash_url').focus();
          }
        },

      /**
       * Shows the "Select YouTube move" dialog
       * @method KWEyoutube
       * @public
       */
      KWEyoutube: function()
        {
        var self = this, 
          dialog, 
          element = self.getSelectedAreaElement();

        if (element)
          {
          self.storeCursor();

          dialog = new KDialog(domiwyg.lang.insert_kwe_youtube, 
            '<form><p><label for="kwe_flash_url">URL</label><input type="text" '
            + 'id="kwe_youtube_url" /></p><p><button type="button" id="kwe_insert_youtube">'
            + 'OK</button></p></form>', 300, 150, function(e, targ)
            {
            if (targ.id === 'kwe_insert_youtube')
              {
              self.restoreCursor();
              element = self.getFirstContainer(element);
              element.container.insertBefore(toDOMnode('<img src="' + Kwf.FULLPATH_SITE
                + '/images/admin/flashlayer.png" width="420" height="315" data-url="'
                + elem('kwe_youtube_url').value + '" class="kwe-youtube-layer" />'), element.reference);
              dialog.close();
              }
            });

          elem('kwe_flash_url').focus();
          }
        },

      /**
       * Finds all textareas wanting to use Domiwyg for replacing Flash objects
       * @method find
       * @public
       */
      find: function()
        {
        var textareas = document.getElementsByTagName('textarea'), 
          t, textarea;

        function replaceObjects(match, tag_name, attributes)
          {
          return '<img src="' + Kwf.FULLPATH_SITE + '/images/admin/flashlayer.png" class="kwe-'
            + (tag_name === 'iframe' ? 'youtube' : 'flash') + '-layer"'
            + attributes.replace(/(data|src)/, 'data-url') + ' />';
          }

        for (t = 0; t < textareas.length; t++)
          {
          textarea = textareas[t];
          if (hasClass(textarea, 'use-domiwyg'))
            {
            textarea.value = textarea.value.replace(/<(iframe|object)([^>]+)>(.*?)<\/(iframe|object)>/ig, replaceObjects);
            domiwyg.create(textarea);
            }
          }
        },

      /**
       * Saves the current content of all Domiwygs on the form in it's corresponding textareas
       * @method saveDomiwyg
       * @public
       * @param {HTMLButtonElement} targ The button that fired the event
       */
      saveDomiwyg: function(targ)
        {
        var textareas = targ.form.getElementsByTagName('textarea'), 
          t, i, 
          dummy = document.createElement('div'), 
          flashes, flash;

        for (t = 0; t < textareas.length; t++)
          {
          if (hasClass(textareas[t], 'has-domiwyg'))
            {
            dummy.innerHTML = textareas[t].domiwyg.save();
            flashes = dummy.querySelectorAll('.kwe-flash-layer, .kwe-youtube-layer');

            for (i = 0; i < flashes.length; i++)
              {
              flash = flashes[i];
              if (flash.className === 'kwe-flash-layer')
                {
                flash.parentNode.insertBefore(toDOMnode('<object type="application/x-shockwave-flash" data="'
                  + flash.getAttribute('data-url') + '" width="' + flash.getAttribute('width')
                  + '" height="' + flash.getAttribute('height') + '">'
                  + '<param name="movie" value="' + flash.getAttribute('data-url') + '" /></object>'), flashes[i]);
                }
              else if (flash.className === 'kwe-youtube-layer')
                {
                flash.parentNode.insertBefore(toDOMnode('<iframe src="' + flash.getAttribute('data-url')
                  + '" width="' + flash.getAttribute('width') + '" height="'
                  + flash.getAttribute('height') + '" frameborder="0" allowfullscreen="true">'
                  + '</iframe>'), flashes[i]);
                }

              flash.parentNode.removeChild(flash);
              }

            textareas[t].value = dummy.innerHTML;
            }
          }
        }
      };
    }(domiwyg)), 

  /**
   * Lists all loaded assets
   * @property assets
   * @private
   * @type Object
   */
   assets = {},

   /**
   * Contains functions for creating image and page link browsers
   * @class Browser
   * @private
   */
  Browser = (function()
    {
    /**
     * Contains reference to current browser button
     * @property current_btn
     * @type HTMLButtonElement
     * @private
     * @default null
     */
    var current_btn = null, 

    /**
     * Contains reference to current browser callback function
     * @property current_callback
     * @type Function
     * @private
     * @default null
     */
    current_callback = null, 

    /**
     * Reference to current dialog
     * @property dialog
     * @type Dialog
     * @private
     * @default null
     */
    dialog = null;

    /**
     * Returns the <input> belonging to the browser
     * @method getInput
     * @public
     * @param {HTMLButtonElement} btn The button clicked
     */
    function getInput(btn)
      {
      return btn.input || btn.parentNode.input;
      }

    /**
     * Returns the <img> belonging to the browser
     * @method getImage
     * @private
     * @param {HTMLButtonElement} btn The button clicked
     */
    function getImage(btn)
      {
      return btn.parentNode.getElementsByTagName('img')[0];
      }

    /**
     * Loads new images and folders into the image browser
     * @method loadImageList
     * @private
     * @param {String} path The path to browse to
     */
    function loadImageList(path)
      {
      var html = '', json, i;

      if (path === '/')
        {
        path = '';
        }

      Ajax.get(Kwf.MODR + 'Upload/js_browse/' + path, function(resp)
        {
        json = resp.page.files;
        json.splice(0, 0, {folder: 1, url: resp.page.up_path || '', name: 'Gå tillbaka'});

        if (resp.page.cd !== '')
          {
          resp.page.cd += '/';
          }

        for (i = 0; i < json.length; i++)
          {
          if (i !== 0 || path !== '')
            {
            html += '<div data-url="' + (i ? resp.page.cd : '') + json[i].url + '" class="kwe-browse-item ';
            if (json[i].folder)
              {
              html += 'kwe-change-folder">' + json[i].name + '</div>';
              }
            else if (/\.(jpg|jpeg|gif|png|bmp|ico)$/i.test(json[i].name))
              {
              html += 'kwe-select-image">' + json[i].name + ' <img src="' + Kwf.FULLPATH_SITE + '/upload/' + json[i].name + '" alt="" /></div>';
              }
            }
          }

        elem('browser_image_browser').innerHTML = html;
        elem('browser_cd').innerHTML = '/' + resp.page.cd;
        }, function() {});
      }

    /**
     * Loads new pages and folders into the page browser
     * @method loadPageList
     * @private
     * @param {Number} language Language ID
     * @param {String} path The path to browse to
     */
    function loadPageList(language, path)
      {
      var html = '', json, i;

      language = parseInt(language, 10);

      if (path === '/')
        {
        path = '';
        }

      Ajax.get(Kwf.MODR + 'page/js_browse/' + language + '/' + path, function(resp)
        {
        json = resp.page.pages;
        json.splice(0, 0, {url: '', title: 'Gå uppåt'});

        for (i = 0; i < json.length; i++)
          {
          if (i !== 0 || path !== '')
            {
            html += '<div data-url="' + json[i].url + '" data-lang="' + language + '" class="kwe-browse-item kwe-change-page">'
              + json[i].title + '<a href="javascript:void(0);" class="kwe-select-page">Välj</a></div>';
            }
          }

        elem('browser_page_browser').innerHTML = html;
        elem('browser_cd').innerHTML = resp.page.cd;
        }, function() {});
      }

    /**
     * Sets selected image and closes the dialog
     * @method setImage
     * @private
     * @param {String} image The image URL
     */
    function setImage(image)
      {
      var src = '/upload/' + image;

      if (current_btn)
        {
        getInput(current_btn).value = src;
        getImage(current_btn).src = Kwf.FULLPATH_SITE + src;
        current_btn = null;
        }
      else
        {
        current_callback(src);
        current_callback = null;
        }

      dialog.close();
      }

    /**
     * Sets selected page and closes the dialog
     * @method setPage
     * @private
     * @param {String} page The page URL
     */
    function setPage(page)
      {
      if (current_btn)
        {
        getInput(current_btn).value = page;
        current_btn = null;
        }
      else
        {
        current_callback(page);
        current_callback = null;
        }

      dialog.close();
      }

    /**
     * Listener for click event on dialogs
     * @method dialogClickListener
     * @private
     * @param {Event} e The event object
     * @param {HTMLElement} targ The element clicked
     */
    function dialogClickListener(e, targ)
      {
      if (hasClass(targ.parentNode, 'kwe-select-image'))
        {
        targ = targ.parentNode;
        }

      if (hasClass(targ, 'kwe-change-folder'))
        {
        loadImageList(targ.getAttribute('data-url') + '/');
        }
      else if (hasClass(targ, 'kwe-select-image'))
        {
        setImage(targ.getAttribute('data-url'));
        }
      else if (hasClass(targ, 'kwe-change-page'))
        {
        loadPageList(targ.getAttribute('data-lang'), targ.getAttribute('data-url') + '/');
        }
      else if (hasClass(targ, 'kwe-select-page'))
        {
        setPage(targ.parentNode.getAttribute('data-url'));
        }
      }

    return {
      /**
       * Finds all <input> that should be browsers
       * @method findBrowsers
       * @public
       */
      find: function()
        {
        var browser_inputs, 
          input, 
          i, 
          browser;

        if (document.querySelectorAll)
          {
          browser_inputs = document.querySelectorAll('.kwe-image-browse, .kwe-link-browse');

          for (i = 0; i < browser_inputs.length; i++)
            {
            input = browser_inputs[i];

            if (hasClass(input, 'kwe-image-browse'))
              {
              removeClass(input, 'kwe-image-browse');
              input.type = 'hidden';

              browser = input.parentNode.insertBefore(toDOMnode('<div class="kwe-browser">'
                  + '<img src="' + Kwf.FULLPATH_SITE + input.value + '" alt="" />'
                  + '<button type="button" class="kwe-browse-image-btn">Bläddra...</button>'
                  + '<button type="button" class="kwe-delete-image-btn">Ta bort</button></div>'), input);
              browser.input = input;
              }
            else
              {
              replaceClass(input, 'kwe-link-browse', 'kwe-link-input');
              browser = input.parentNode.insertBefore(toDOMnode('<button type="button" class="kwe-browse-page-btn">Bläddra...</button>'), input);
              browser.input = input;
              }
            }
          }
        },

      /**
       * Finds all <input> that should be browsers
       * @method removeImage
       * @public
       * @param {HTMLButtonElement} btn The button clicked
       */
      removeImage: function(btn)
        {
        getInput(btn).value = '';
        getImage(btn).src = '';
        },

      /**
       * Opens an image browser
       * @method openImageBrowser
       * @public
       * @param {Function/HTMLButtonElement} callback The button clicked OR the function to call when image is selected
       */
      openImageBrowser: function(callback)
        {
        if (typeof callback === 'function')
          {
          current_callback = callback;
          }
        else
          {
          current_btn = callback;
          }

        dialog = new KDialog(domiwyg.lang.insert_image, '<p id="browser_cd">/</p>'
          + '<div id="browser_image_browser"></div>', 600, 450, dialogClickListener);

        loadImageList('');
        },

      /**
       * Opens an page browser
       * @method openPageBrowser
       * @public
       * @param {Function/HTMLButtonElement} callback The button clicked OR the function to call when page is selected
       */
      openPageBrowser: function(callback)
        {
        if (typeof callback === 'function')
          {
          current_callback = callback;
          }
        else
          {
          current_btn = callback;
          }

        dialog = new KDialog(domiwyg.lang.create_kwe_link, '<p id="browser_cd">/</p>'
          + '<p><select id="browser_page_language"></select></p>'
          + '<div id="browser_page_browser"></div>', 500, 450, dialogClickListener);

        // Load available languages
        Ajax.get(Kwf.MODR + 'Languages/js_browse/', function(resp)
          {
          var json = resp.page.languages, 
            language_select = elem('browser_page_language'), 
            default_lang_id = 0, 
            i;

          // Listen for change events on the language select
          addEvent(language_select, 'change', function()
            {
            loadPageList(language_select.value, '');
            });

          for (i = 0; i < json.length; i++)
            {
            if (i === 0)
              {
              default_lang_id = json[0].id;
              }

            language_select.options[i] = new Option(json[i].name, json[i].id);
            }

          // Load all pages in root with first language
          loadPageList(default_lang_id, '');
          }, function() {});
        }
      };
    }());

  /**
   * Used by File uploader control. Uploads the selected file with AJAX
   * @method uploadFile
   * @private
   * @param {Event} e The event object
   */
  function uploadFile(e)
    {
    returnFalse(e);

    Ajax.upload(getTarget(e).action, function(response)
      {
      Boxing.hide();
      content_request.parseResponse(response);
      }, boxing_request.parseResponse, elem('file'));
    }

  /**
   * Changes the active menu-item when it's clicked
   * @method changeActiveMenuLink
   * @private
   * @param {HTMLAnchorElement} link The link that was clicked
   */
  function changeActiveMenuLink(link)
    {
    var links = elem('navigation').getElementsByTagName('a'), 
      l;

    for (l = 0; l < links.length; l++)
      {
      removeClass(links[l], 'active');
      }

    addClass(link, 'active');
    }

  /**
   * Changes the page title on ContentRequest load
   * @method changePageTitle
   * @private
   */
  function changePageTitle()
    {
    var title = document.title, 
      new_page_title = '';

    try
      {
      new_page_title = elem('content').getElementsByTagName('h1')[0].innerHTML;
      }
    catch (ex) {}

    document.title = title.substring(0, title.indexOf(' :: ') + 4) + new_page_title;
    }

  /**
   * Loads script and stylesheet assets from Content and BoxingRequest
   * @method loadAssets
   * @private
   */
  function loadAssets()
    {
    var context, 
      scripts, 
      new_elem, 
      links, 
      i, 
      head = document.getElementsByTagName('head')[0];

    if (this instanceof ContentRequest)
      {
      context = elem('content');
      }
    else if (this instanceof BoxingRequest)
      {
      context = Boxing.getWindow();
      }

    scripts = context.getElementsByTagName('script');

    for (i = 0; i < scripts.length; i++)
      {
      if (typeof assets[scripts[i].src] === 'undefined')
        {
        assets[scripts[i].src] = 1;

        new_elem = document.createElement('script');
        new_elem.src = scripts[i].src;
        head.appendChild(new_elem);
        }
      }

    links = context.getElementsByTagName('link');

    for (i = 0; i < links.length; i++)
      {
      if (typeof assets[links[i].href] === 'undefined')
        {
        assets[links[i].href] = 1;

        new_elem = document.createElement('link');
        new_elem.href = links[i].href;
        new_elem.rel = links[i].rel;
        head.appendChild(new_elem);
        }
      }
    }

  /* Add listeners to the KWF click event */
  Kwf.onclick = function(e, targ)
    {
    if (hasClass(targ, 'menu-item') && !hasClass(targ, 'active'))
      {
      changeActiveMenuLink(targ);
      }

    if (hasClass(targ, 'save-domiwyg'))
      {
      Wysiwyg.saveDomiwyg(targ);
      }
    else if (hasClass(targ, 'clink'))
      {
      contentLink(e, targ);
      }
    else if (hasClass(targ, 'blink'))
      {
      boxingLink(e, targ);
      }
    else if (hasClass(targ, 'delete-link'))
      {
      boxing_request.load(e, targ.getAttribute('href'), 300, 200);
      }
    else if (hasClass(targ, 'kwe-browse-image-btn'))
      {
      Browser.openImageBrowser(targ);
      }
    else if (hasClass(targ, 'kwe-delete-image-btn'))
      {
      Browser.removeImage(targ);
      }
    else if (hasClass(targ, 'kwe-browse-page-btn'))
      {
      Browser.openPageBrowser(targ);
      }
    };

  /* Add listeners to the KWF load event */
  Kwf.onload = function()
    {
    if (elem('login_form'))
      {
      elem('username').focus();
      }
    else
      {
      Ajax.setBeforeCallback(beforeAjax);
      Ajax.setAfterCallback(afterAjax);
      }

    Wysiwyg.init();

    addEvent(content_request, 'afterload', noRespContent);
    addEvent(content_request, 'ready', Wysiwyg.find);
    addEvent(content_request, 'ready', changePageTitle);
    addEvent(content_request, 'ready', loadAssets);
    addEvent(content_request, 'ready', Browser.find);

    addEvent(boxing_request, 'afterload', noRespContent);
    addEvent(boxing_request, 'ready', loadAssets);
    addEvent(boxing_request, 'ready', Browser.find);

    addEvent(boxing_request, 'ready', function()
      {
      Wysiwyg.find();
      if (elem('upload_form'))
        {
        addSubmitEvent(elem('upload_form'), uploadFile);
        }
      });

    addEvent(boxing_request, 'afterload', function(e)
      {
      var targ = e.target;
      if (hasClass(targ, 'content-on-close'))
        {
        if (hasClass(targ, 'also-success') || !(this.response.page.errors || this.response.page.infos))
          {
          content_request.parseResponse(this.response);
          this.response.page = '';
          }
        }
      });
    };

  /* Expose some objects to public */
  return {
    Browser: Browser
    };
  }(window, document, elem, content_request, boxing_request, Boxing));