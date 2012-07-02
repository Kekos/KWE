/**
 * KWF Script: kwe.js
 * Based on DOMcraft
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-07-02
 * @version 3.0
 */

Kwf.onclick = function(e, targ)
  {
  var k = kwe,
    cls = targ.className, 
    breq = boxing_request;

  if (hasClass(targ, 'save-domiwyg'))
    k.saveDomiwyg(e, targ);
  else if (hasClass(targ, 'clink'))
    k.contentLink(e, targ);
  else if (hasClass(targ, 'blink'))
    k.boxingLink(e, targ);
  else if (hasClass(targ, 'delete-link'))
    breq.load(e, targ.getAttribute('href'), 300, 200);
  else if (targ.name == 'select-page')
    elem('dw_link_url').value = targ.parentNode.getAttribute('data-url');
  else if (hasClass(targ, 'browse-to-link'))
    k.KWElinkBrowse(targ.parentNode.getAttribute('data-url'));
  else if (targ.name == 'select-image')
    elem('dw_img_url').value = targ.parentNode.getAttribute('data-url');
  else if (hasClass(targ, 'browse-to-img'))
    k.KWEimageBrowse(targ.parentNode.getAttribute('data-url'));
  };

Kwf.onload = function(e)
  {
  var k = kwe;

  if (elem('login_form'))
    {
    elem('username').focus();
    }
  else
    {
    Ajax.setBeforeCallback(k.beforeAjax);
    Ajax.setAfterCallback(k.afterAjax);
    }

  k.initWysiwygs();

  addEvent(content_request, 'afterload', k.noRespContent);
  addEvent(content_request, 'ready', domiwyg.find);

  addEvent(boxing_request, 'afterload', k.noRespContent);
  addEvent(boxing_request, 'ready', domiwyg.find);
  addEvent(boxing_request, 'afterload', function(e)
    {
    var targ = e.target;
    if (hasClass(targ, 'content-on-close'))
      {
      content_request.parseResponse(this.response);
      this.response.page = '';
      }
    });
  };

var kwe = {
  beforeAjax: function()
    {
    var ajax_loader = elem('ajax_loader'),
      doc = document;

    if (!ajax_loader)
      {
      ajax_loader = doc.createElement('div');
      ajax_loader.id = 'ajax_loader';
      ajax_loader.appendChild(doc.createTextNode('Laddar...'));
      elem('header').appendChild(ajax_loader);
      }

    ajax_loader.style.display = 'block';
    },

  afterAjax: function()
    {
    elem('ajax_loader').style.display = 'none';
    },

  contentLink: function(e, link)
    {
    content_request.load(e, link.getAttribute('href'));
    },

  boxingLink: function(e, link)
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
    },

  noRespContent: function(e)
    {
    var targ = e.target;
    if (hasClass(targ, 'no-content'))
      this.response.page.content = '';
    },

  initWysiwygs: function()
    {
    if (typeof domiwyg != 'undefined')
      {
      var dw = domiwyg;

      dw.lang.create_kwe_link = 'Infoga länk till en annan sida på denna webbplats';
      dw.lang.insert_kwe_image = 'Infoga bild som finns uppladdat till KWE';
      dw.tool_btns.splice(6, 0, ['KWElink', 'create_kwe_link'], ['KWEimage', 'insert_kwe_image']);
      dw.area.prototype.cmdKWElink = kwe.KWElink;
      dw.area.prototype.cmdKWEimage = kwe.KWEimage;
      }
    },

  KWElink: function()
    {
    var self = this, lang = domiwyg.lang, 
      element = self.getSelectedAreaElement(), 
      node_name = null;

    if (element)
      {
      node_name = element.nodeName.toLowerCase();
      self.storeCursor();

      Boxing.show('<h1>' + lang.create_link + '</h1>'
        + '<p>Bläddra dig fram till den interna sida du vill länka till.</p><p id="dw_KWE_cd">/</p><ul id="dw_KWE_page_browser"></ul>'
        + '<p><input type="hidden" id="dw_link_protocol" value="" /><input type="text" id="dw_link_url" value="" /></p>'
        + '<p>' + lang.info_link_delete + '</p>'
        + '<p><button id="btn_create_link" class="hide-boxing">' + lang.ok + '</button> <button class="hide-boxing">' + lang.cancel + '</button></p>', 500, 400);

      kwe.KWElinkBrowse('');

      if (node_name)
        {
        if (node_name != 'a')
          element = null;

        addEvent(elem('btn_create_link'), 'click', function()
          {
          elem('dw_link_url').value = Kwf.MODR_SITE + elem('dw_link_url').value + '/';
          self.createLink(element);
          });
        }
      else
        {
        Boxing.hide();
        }
      }
    },

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

  KWEimage: function()
    {
    var self = this, lang = domiwyg.lang;

    if (self.getSelectedAreaElement())
      {
      self.storeCursor();

      Boxing.show('<h1>' + lang.insert_image + '</h1>'
        + '<p>Bläddra dig fram till den bildfil du vill infoga.</p><p id="dw_KWE_cd">/</p><ul id="dw_KWE_image_browser"></ul>'
        + '<p>' + lang.image_url + ': <input type="text" id="dw_img_url" value="" /></p>'
        + '<p><button id="btn_insert_image" class="hide-boxing">' + lang.ok + '</button> <button class="hide-boxing">' + lang.cancel + '</button></p>', 500, 400);

      kwe.KWEimageBrowse('');

      addEvent(elem('btn_insert_image'), 'click', function()
        {
        elem('dw_img_url').value = Kwf.FULLPATH_SITE + '/upload/' + elem('dw_img_url').value;
        self.insertImage();
        }, self);
      }
    },

  KWEimageBrowse: function(path)
    {
    var html = '', json, i;

    Ajax.get(Kwf.MODR + 'upload/js_browse/' + path, function(resp)
      {
      json = resp.page.files;
      json.splice(0, 0, {folder: 1, url: resp.page.up_path || '', name: 'Gå uppåt'});

      if (resp.page.cd != '')
        resp.page.cd += '/';

      for (i = 0; i < json.length; i++)
        {
        html += '<li data-url="' + (i ? resp.page.cd : '') + json[i].url + '">';
        if (json[i].folder)
          html += ' <a href="javascript: void(0);" class="browse-to-img">' + json[i].name + '</a></li>';
        else
          html += (i ? '<input type="radio" name="select-image" /> ' : '') + '' + json[i].name + '</li>';
        }

      elem('dw_KWE_image_browser').innerHTML = html;
      elem('dw_KWE_cd').innerHTML = '/' + resp.page.cd;
      }, function() {});
    },

  saveDomiwyg: function(e, targ)
    {
    var textareas = targ.form.getElementsByTagName('textarea'), 
      t;

    for (t = 0; t < textareas.length; t++)
      {
      if (hasClass(textareas[t], 'has-domiwyg'))
        textareas[t].value = textareas[t].domiwyg.save();
      }
    }
  };