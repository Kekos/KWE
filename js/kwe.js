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

  addEvent(content_request, 'afterload', k.noRespContent);
  addEvent(content_request, 'ready', k.findCollapsables);
  addEvent(content_request, 'ready', k.initWysiwygs);

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
      domiwyg.find();
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