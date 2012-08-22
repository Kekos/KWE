/*!
 * KWF Script: kwe.js
 * Based on DOMcraft
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-08-21
 * @version 3.0
 */

/* JSLint: First asume we have the KWF Framework */
/*global elem, getTarget, returnFalse, addEvent, removeEvent, addSubmitEvent, 
   previousNode, nextNode, firstChildElement, lastChildElement, hasClass, 
   addClass, removeClass, giveOpacity, parseJSON, var_dump, toDOMnode, Ajax, 
   Boxing, Kwf, KWFEventTarget, content_request, boxing_request */

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
          domiwyg.tool_btns.splice(6, 0, ['KWElink', 'create_kwe_link'], ['KWEimage', 'insert_kwe_image']);
          domiwyg.area.prototype.cmdKWElink = Wysiwyg.KWElink;
          domiwyg.area.prototype.cmdKWEimage = Wysiwyg.KWEimage;
          }
        },

      /**
       * Shows the "Select KWE Link" dialog. Let's the user select an internal 
       * page to link to
       * @method KWElink
       * @public
       * @param {HTMLButtonElement} btn The button who fired the event
       */
      KWElink: function(btn)
        {
        var self = this, lang = domiwyg.lang, 
          element = self.getSelectedAreaElement(), 
          node_name = null;

        if (element)
          {
          node_name = element.nodeName.toLowerCase();
          self.storeCursor();

          domiwyg.showDialog('<h1>' + lang.create_link + '</h1>'
            + '<p>Bläddra dig fram till den interna sida du vill länka till.</p><p id="dw_KWE_cd">/</p><ul id="dw_KWE_page_browser"></ul>'
            + '<p><input type="hidden" id="dw_link_protocol" value="" /><input type="text" id="dw_link_url" value="" /></p>'
            + '<p>' + lang.info_link_delete + '</p>'
            + '<p><button id="btn_create_link" class="hide-dialog">' + lang.ok + '</button> <button class="hide-dialog">' + lang.cancel + '</button></p>', btn);

          Wysiwyg.KWElinkBrowse('');

          if (node_name)
            {
            if (node_name !== 'a')
              {
              element = null;
              }

            addEvent(elem('btn_create_link'), 'click', function()
              {
              elem('dw_link_url').value = Kwf.MODR_SITE + elem('dw_link_url').value + '/';
              self.createLink(element);
              });
            }
          else
            {
            domiwyg.hideDialog();
            }
          }
        },

      /**
       * Loads new pages into the "Select link" dialog
       * @method KWElinkBrowse
       * @public
       * @param {String} path The path to browse to
       */
      KWElinkBrowse: function(path)
        {
        var html = '', json, i;

        Ajax.get(Kwf.MODR + 'page/js_browse/' + path, function(resp)
          {
          json = resp.page.pages;
          json.splice(0, 0, {url: '', title: 'Gå uppåt'});

          for (i = 0; i < json.length; i++)
            {
            html += '<li data-url="' + json[i].url + '">' + (i ? '<input type="radio" name="select-page" /> ' : '') + '<a href="javascript: void(0);" class="browse-to-link">' + json[i].title + '</a></li>';
            }

          elem('dw_KWE_page_browser').innerHTML = html;
          elem('dw_KWE_cd').innerHTML = resp.page.cd;
          }, function() {});
        },

      /**
       * Shows the "Select KWE Image" dialog. Let's the user select an uploaded
       * image to embed
       * @method KWEimage
       * @public
       * @param {HTMLButtonElement} btn The button who fired the event
       */
      KWEimage: function(btn)
        {
        var self = this, lang = domiwyg.lang;

        if (self.getSelectedAreaElement())
          {
          self.storeCursor();

          domiwyg.showDialog('<h1>' + lang.insert_image + '</h1>'
            + '<p>Bläddra dig fram till den bildfil du vill infoga.</p><p id="dw_KWE_cd">/</p><ul id="dw_KWE_image_browser"></ul>'
            + '<p>' + lang.image_url + ': <input type="text" id="dw_img_url" value="" /></p>'
            + '<p><button id="btn_insert_image" class="hide-dialog">' + lang.ok + '</button> <button class="hide-dialog">' + lang.cancel + '</button></p>', btn);

          Wysiwyg.KWEimageBrowse('');

          addEvent(elem('btn_insert_image'), 'click', function()
            {
            elem('dw_img_url').value = Kwf.FULLPATH_SITE + '/upload/' + elem('dw_img_url').value;
            self.insertImage();
            }, self);
          }
        },

      /**
       * Loads new images and folders into the "Select image" dialog
       * @method KWEimageBrowse
       * @public
       * @param {String} path The path to browse to
       */
      KWEimageBrowse: function(path)
        {
        var html = '', json, i;

        Ajax.get(Kwf.MODR + 'Upload/js_browse/' + path, function(resp)
          {
          json = resp.page.files;
          json.splice(0, 0, {folder: 1, url: resp.page.up_path || '', name: 'Gå uppåt'});

          if (resp.page.cd !== '')
            {
            resp.page.cd += '/';
            }

          for (i = 0; i < json.length; i++)
            {
            html += '<li data-url="' + (i ? resp.page.cd : '') + json[i].url + '">';
            if (json[i].folder)
              {
              html += ' <a href="javascript: void(0);" class="browse-to-img">' + json[i].name + '</a></li>';
              }
            else
              {
              html += (i ? '<input type="radio" name="select-image" /> ' : '') + json[i].name + '</li>';
              }
            }

          elem('dw_KWE_image_browser').innerHTML = html;
          elem('dw_KWE_cd').innerHTML = '/' + resp.page.cd;
          }, function() {});
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
          t;

        for (t = 0; t < textareas.length; t++)
          {
          if (hasClass(textareas[t], 'has-domiwyg'))
            {
            textareas[t].value = textareas[t].domiwyg.save();
            }
          }
        }
      };
    }(domiwyg));

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
    else if (targ.name === 'select-page')
      {
      elem('dw_link_url').value = targ.parentNode.getAttribute('data-url');
      }
    else if (hasClass(targ, 'browse-to-link'))
      {
      Wysiwyg.KWElinkBrowse(targ.parentNode.getAttribute('data-url'));
      }
    else if (targ.name === 'select-image')
      {
      elem('dw_img_url').value = targ.parentNode.getAttribute('data-url');
      }
    else if (hasClass(targ, 'browse-to-img'))
      {
      Wysiwyg.KWEimageBrowse(targ.parentNode.getAttribute('data-url'));
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
    addEvent(content_request, 'ready', domiwyg.find);
    addEvent(content_request, 'ready', changePageTitle);

    addEvent(boxing_request, 'afterload', noRespContent);

    addEvent(boxing_request, 'ready', function()
      {
      domiwyg.find();
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
  }(window, document, elem, content_request, boxing_request, Boxing));