/**
 * KWF Script: kwe.js
 * Based on DOMcraft
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2011-06-29
 * @version 3.0
 */

kwf.onclick = function(e, targ)
  {
  var k = kwe,
    cls = targ.className, 
    breq = boxing_request;

  if (hasClass(targ, 'save-domiwyg'))
    k.saveDomiwyg(e, targ);
  else if (hasClass(targ, 'toggle-collapse'))
    k.toggleCollapse(e, targ);
  else if (hasClass(targ, 'clink'))
    k.contentLink(e, targ);
  else if (hasClass(targ, 'blink'))
    k.boxingLink(e, targ);
  else if (hasClass(targ, 'delete-link'))
    breq.load(e, targ.getAttribute('href'), 300, 200);
  else if (hasClass(targ, 'delete-controller'))
    {
    returnFalse(e);
    breq.width = 300;
    breq.height = 200;
    ajax.post(targ.form.action, breq.parseResponse, breq.parseResponse, 
        {controller_id: targ.form.controller_id.value, delete_controller: 1});
    }
  else if (targ.name == 'select-page')
    elem('dw_link_url').value = targ.parentNode.getAttribute('data-url');
  else if (hasClass(targ, 'browse-to'))
    k.KWElinkBrowse(targ.parentNode.getAttribute('data-url'));
  };

kwf.onload = function(e)
  {
  var k = kwe;

  if (elem('login_form'))
    {
    elem('username').focus();
    }
  else
    {
    ajax.onbeforeajax = k.beforeAjax;
    ajax.onafterajax = k.afterAjax;
    }

  k.initWysiwygs();

  addEvent(content_request, 'afterload', k.noRespContent);
  addEvent(content_request, 'ready', k.findCollapsables);
  addEvent(content_request, 'ready', domiwyg.find);

  addEvent(boxing_request, 'afterload', k.noRespContent);
  addEvent(boxing_request, 'afterload', function(e)
    {
    var targ = e.target;
    if (hasClass(targ, 'delete-dialog'))
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

  findCollapsables: function()
    {
    var sections = document.getElementsByTagName('section'), 
      fieldsets = document.getElementsByTagName('fieldset'), 
      s, section, h2;

    function prepare(element)
      {
      if (hasClass(element, 'collapsable'))
        {
        element.className = element.className.replace(/\bcollapsable\b/, '');
        h2 = element.getElementsByTagName('h2')[0];
        h2.innerHTML = '<a href="" class="toggle-collapse">' + h2.innerHTML + '</a>';
        if (hasClass(element, 'start-collapsed'))
          {
          removeClass(element, 'start-collapsed');
          addClass(element, 'collapsed');
          }
        }
      }

    for (s = 0; s < sections.length; s++)
      {
      prepare(sections[s]);
      }

    for (s = 0; s < fieldsets.length; s++)
      {
      prepare(fieldsets[s]);
      }
    },

  toggleCollapse: function(e, toggler)
    {
    returnFalse(e);
    var section = toggler.parentNode.parentNode;

    if (hasClass(section, 'collapsed'))
      removeClass(section, 'collapsed');
    else
      addClass(section, 'collapsed');
    },

  contentLink: function(e, link)
    {
    content_request.load(e, link.getAttribute('href'));
    },

  boxingLink: function(e, link)
    {
    var temp, width = 400, height = 300, 
      cls = link.className;

    if (cls.indexOf('dim') > -1)
      {
      temp = cls.substring(cls.indexOf('dim') + 3);
      width = temp.substring(0, temp.indexOf('x'));
      height = temp.substring(temp.indexOf('x') + 1, cls.indexOf(' ') - 2);
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

      dw.tool_btns.splice(1, 0, ['KWElink', 'create_kwe_link']);
      dw.area.prototype.cmdKWElink = kwe.KWElink;
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

      boxing.show('<h1>' + lang.create_link + '</h1>'
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
          elem('dw_link_url').value = kwf.MODR_SITE + elem('dw_link_url').value + '/';
          self.createLink(element);
          });
        }
      else
        {
        boxing.hide();
        }
      }
    },

  KWElinkBrowse: function(path)
    {
    var html = '', json, i;

    ajax.get(kwf.MODR + 'page/js_browse/' + path, function(resp)
      {
      json = resp.page.pages;
      json.splice(0, 0, {url: '', title: 'Gå uppåt'});

      for (i = 0; i < json.length; i++)
        {
        html += '<li data-url="' + json[i].url + '">' + (i ? '<input type="radio" name="select-page" /> ' : '') + '<a href="javascript: void(0);" class="browse-to">' + json[i].title + '</a></li>';
        }

      elem('dw_KWE_page_browser').innerHTML = html;
      elem('dw_KWE_cd').innerHTML = resp.page.cd;
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