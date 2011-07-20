/* Leetitor - Rich text editor */
/* Copyright, Christoffer Lindahl, 2008 */

/* Global functions */

function elem(id)
  {
  try
    {
    return document.getElementById(id) || document.all[id];
    }
  catch (e)
    {
    return false;
    }
  }

function addEvent(elem, state, func)
  {
  if (elem.addEventListener)
    elem.addEventListener(state, func, false);
  else if (elem.attachEvent)
    elem.attachEvent('on' + state, func);
  }

function getTarget(e)
  {
  var e = e || window.event;
  return e.target || e.srcElement;
  }

function getOffsetTop(elem)
  {
  var off_top = elem.offsetTop,
    off_parent = elem.offsetParent;

  while (off_parent)
    {
    off_top += off_parent.offsetTop;
    off_parent = off_parent.offsetParent;
    }
  return off_top;
  }

function getOffsetLeft(elem)
  {
  var off_left = elem.offsetLeft,
    off_parent = elem.offsetParent;

  while (off_parent)
    {
    off_left += off_parent.offsetLeft;
    off_parent = off_parent.offsetParent;
    }
  return off_left;
  }

function selection(win, doc)
  {
  if (win.getSelection)
    return win.getSelection();
  else if (doc.selection)
    return doc.selection;
  }

function toRange(sel)
  {
  if (sel.getRangeAt)
    return sel.getRangeAt(0);
  else if (document.selection)
    return sel.createRange();
  else
    {
    var range = document.createRange();
    range.setStart(sel.anchorNode, sel.anchorOffset);
    range.setEnd(sel.focusNode, sel.focusOffset);
    return range;
    }
  }

/* Leetitor */

var leetitor = {
  base_src: '',

  /* Find textareas to apply to */

  init: function()
    {
    // Find textareas wanting an editor
    var textareas = document.getElementsByTagName('textarea'),
      t, new_id, editor_frame, inputs, n;

    for (t = 0; t < textareas.length; t++)
      {
      if (textareas[t].className.indexOf('editable') >= 0)
        {
        textareas[t].style.display = 'none';
        new_id = textareas[t].id + '_editable';

        editor_frame = document.createElement('iframe');
        editor_frame.setAttribute('id', new_id);
        editor_frame.setAttribute('name', new_id);
        editor_frame.className = 'leetitor';
        editor_frame.setAttribute('src', leetitor.base_src + 'leetitor/leetitor.html?element=' + textareas[t].id);
        if (document.all)
          editor_frame.frameBorder = 0;
        editor_frame.style.width = '100%';
        editor_frame.style.height = '380px';
        editor_frame.style.border = '1px solid #000';
        textareas[t].parentNode.appendChild(editor_frame);

        inputs = textareas[t].parentNode.getElementsByTagName('input');
        // Hide submit buttons
        for (n = 0; n < inputs.length; n++)
          {
          if (inputs[n].type == 'submit' && inputs[n].className.indexOf('no-hide') < 0)
            {
            inputs[n].style.display = 'none';
            }
          }

        textareas[t].className = textareas[t].className.replace('editable', '');
        }
      }
    },

  /* Start up an editor */

  id: null,
  doc: null,
  win: null,
  settings: [],
  sizes: [1, 2, 3, 4, 5, 6, 7],
  sizes_locale: ['7.5pt', '10pt', '12pt', '13.5pt', '18pt', '24pt', '36pt'],
  color_state: 0,
  color_btn: null,
  dropcmd: null,
  dropmenu: null,
  source: false,

  start: function()
    {
    var leet = leetitor, search = window.location.search,
      settings, s, parts, style_selector, style_menu, f, stle, cls, format_selector,
      format_menu, format, fontname_selector, fontname_menu, fo, font,
      fontsize_selector, fontsize_menu, size, e;

    leet.id = search.substring(search.indexOf('element=') + 8, search.length);
    if (document.all)
      elem('editor').frameBorder = 2;

    // Get settings from parent
    settings = parent.elem(leet.id + '_settings').value;
    settings = settings.split(';');
    for (s in settings)
      {
      parts = settings[s].split('=');
      leet.settings[parts[0]] = parts[1];
      }

    // Any extra toolbars?
    if (leet.settings['style'] == 'true')
      {
      style_selector = elem('selector_style');
      style_selector.style.display = 'block';
      style_menu = elem('menu_style');
      style_menu.style.top = getOffsetTop(style_selector) + 20 + 'px';
      style_menu.style.left = getOffsetLeft(style_selector) + 'px';
      for (f in leet.styles)
        {
        stle = document.createElement('div');
        cls = leet.styles[f][2];
        cls = (cls == '') ? 'nothing' : cls;
        stle.className = 'dropitem style_' + f + ' ' + cls;
        stle.appendChild(document.createTextNode(leet.styles[f][0]));
        style_menu.appendChild(stle);
        }
      }

    if (leet.settings['format'] == 'true')
      {
      format_selector = elem('selector_formatblock');
      format_selector.style.display = 'block';
      format_menu = elem('menu_formatblock');
      format_menu.style.top = getOffsetTop(format_selector) + 20 + 'px';
      format_menu.style.left = getOffsetLeft(format_selector) + 'px';
      for (f in leet.formats)
        {
        format = document.createElement('div');
        format.className = 'dropitem ' + leet.formats[f];
        format.appendChild(document.createTextNode(leet.formats_locale[f]));
        format_menu.appendChild(format);
        }
      }

    if (leet.settings['font'] == 'true')
      {
      fontname_selector = elem('selector_fontname');
      fontname_selector.style.display = 'block';
      fontname_menu = elem('menu_fontname');
      fontname_menu.style.top = getOffsetTop(fontname_selector) + 20 + 'px';
      fontname_menu.style.left = getOffsetLeft(fontname_selector) + 'px';
      for (f in leet.fonts)
        {
        fo = leet.fonts[f];
        font = document.createElement('div');
        font.className = 'dropitem ' + fo;
        font.style.fontFamily = fo;
        font.appendChild(document.createTextNode(fo));
        fontname_menu.appendChild(font);
        }

      fontsize_selector = elem('selector_fontsize');
      fontsize_selector.style.display = 'block';
      fontsize_menu = elem('menu_fontsize');
      fontsize_menu.style.top = getOffsetTop(fontsize_selector) + 20 + 'px';
      fontsize_menu.style.left = getOffsetLeft(fontsize_selector) + 'px';
      for (f in leet.sizes)
        {
        s = leet.sizes_locale[f];
        size = document.createElement('div');
        size.className = 'dropitem ' + leet.sizes[f];
        size.style.fontSize= s;
        size.appendChild(document.createTextNode(s));
        fontsize_menu.appendChild(size);
        }
      }

    if (leet.settings['colors'] == 'false')
      {
      elem('forecolor').style.display = 'none';
      elem('hilitecolor').style.display = 'none';
      }

    if (leet.settings['link'] == 'false')
      elem('createlink').style.display = 'none';

    if (leet.settings['table'] == 'false')
      elem('inserttable').style.display = 'none';

    leet.makeEditable(true);

    if (document.all) // Prevent selecting command buttons
      {
      for (e = 0; e < document.all.length; e++)
        document.all(e).unselectable = 'on';
      document.all['editor'].unselectable = 'off';
      }
    },

  /* Make the editor editable */

  makeEditable: function(adv)
    {
    var leet = leetitor;

    leet.doc = elem('editor').contentDocument;
    leet.win = elem('editor').contentWindow;
    if (typeof leet.doc == 'undefined')
      leet.doc = leet.win.document;

    leet.doc.designMode = 'on';
    // Bug fixes for several browsers, before entering data to editor
    window.setTimeout(function()
      {
      leet.doc.body.innerHTML = (adv) ? parent.elem(leet.id).value : '';
      if (navigator.userAgent.indexOf('Gecko') > -1) // Sorry, some browser targeting here
        {
        leet.doc.designMode = 'off';
        leet.doc.designMode = 'on';
        }
      if (adv)
        {
        addEvent(document.body, 'click', leet.click);
        addEvent(window.frames['editor'].document.body, 'click', leet.click);
        addEvent(leet.doc, 'keyup', leet.senseFormats);
        addEvent(leet.doc, 'keydown', leet.keys);
        }
      }, 0);
    },

  /* Handle clicking in editor */

  click: function(e)
    {
    var targ = getTarget(e),
      btn_cmd, leet = leetitor;

    /* Toolbars */
    if (targ.nodeName.toLowerCase() == 'button' && targ.className.indexOf('cmd') == 0)
      {
      btn_cmd = targ.id;
      if (btn_cmd == 'save')
        leet.save();
      else if (btn_cmd == 'source')
        leet.switchSource();
      else if (btn_cmd == 'attr_editor' && !leet.source)
        leet.openWin('dialog_attr_editor.html', 'dialog_attr_editor', 500, 400);
      else if (btn_cmd == 'createlink' && !leet.source)
        leet.openWin('dialog_createlink.html', 'dialog_createlink', 400, 150);
      else if (btn_cmd == 'insertimage' && !leet.source)
        leet.openWin('dialog_insertimage.html', 'dialog_insertimage', 400, 150);
      else if (btn_cmd == 'inserttable' && !leet.source)
        leet.openWin('dialog_inserttable.html', 'dialog_inserttable', 350, 450);
      else if (btn_cmd.indexOf('color') > 0 && !leet.source)
        {
        leet.color(btn_cmd, targ);
        return;
        }
      else if (!leet.source)
        leet.format(btn_cmd);
      }

    if (targ.nodeName.toLowerCase() == 'div' && targ.id.indexOf('selector_') == 0 && !leet.source)
      leet.dropdown(targ.id.substring(9, 30), targ); // Show dropdown
    else if (targ.className.indexOf('dropdown_') < 0)
      leet.dropdown(null, null); // To hide format menu

    if (targ.className.indexOf('dropitem') == 0 && !leet.source)
      leet.advFormat(targ); // To make advanced formating

    if (targ.parentNode.id != 'colorpalette')
      leet.color(null, targ);  // To hide color palette
    else if (targ.parentNode.id == 'colorpalette' && !leet.source)
      leet.formatColor(targ.style.backgroundColor); // To select color and format text

    leet.senseFormats();
    },

  /* Handle key press in editor */

  keys: function(e)
    {
    var key = e.keyCode, leet = leetitor;
    if ((e.ctrlKey && key == 86) && !leet.source)
      {
      window.setTimeout(function() {leet.doc.body.innerHTML = leet.paste();}, 1);
      }
    },

  /* Save the document */

  save: function()
    {
    if (leetitor.source)
      {
      alert(leetitor.lang['err_save_source']);
      return false;
      }

    var doc = leetitor.paste(), // Make nicer code!
      tarea = parent.elem(leetitor.id),
      parent_tarea = tarea.parentNode,
      leet = leetitor;

    doc = leet.fixMarkup(doc); // ...and here
    tarea.value = doc; // Put the document into textarea
    if (leet.settings['save'] == 'submit')
      {
      while (parent_tarea.nodeName.toLowerCase() != 'form')
        parent_tarea = parent_tarea.parentNode;
      parent_tarea.submit(); // Just submit the form
      }
    else
      {
      parent.LeetitorSaveDoc(); // Call an user-defined function
      }
    },

  /* Basic formating */

  format: function(cmd)
    {
    try
      {
      leetitor.doc.execCommand(cmd, false, null);
      }
    catch (e)
      {
      alert(leetitor.lang['err_format_support1'] + cmd + leetitor.lang['err_format_support2']);
      }
    leetitor.focus();
    },

  /* Advanced formating */

  advFormat: function(targ)
    {
    var cmd = targ.parentNode.id.substring(5, 30),
      value = targ.className.substring(9, 40),
      leet = leetitor;
    if (cmd == 'style')
      return leet.styleFormat(targ);

    if (cmd == 'formatblock')
      value = '<' + value + '>';

    try
      {
      leet.doc.execCommand(cmd, false, value);
      }
    catch (e)
      {
      alert(leet.lang['err_format_support1'] + cmd + leet.lang['err_format_support2']);
      }
    leet.focus();
    },

  /* Style formating */

  styleFormat: function(targ)
    {
    // This method is not clean. It may mess up the mark-up!
    var cmd = targ.className.substring(15, 18);
    cmd = cmd.substring(0, cmd.indexOf(' '));
    var leet = leetitor,
      stle = leet.styles[cmd],
      sel = selection(leet.win, leet.doc),
      range = toRange(sel),
      sel_frag, new_elem, li, cls, html, new_elem;

    if (window.getSelection)
      {
      if (!sel.toString().length)
        return;
      sel_frag = range.extractContents() || document.createDocumentFragment();

      new_elem = document.createElement(stle[1]);
      if (stle[2] != '')
        new_elem.className = stle[2];
      if (stle[1] == 'ul' || stle[1] == 'ol') // Creating lists require list elements too!
        {
        li = document.createElement('li');
        li.appendChild(sel_frag);
        new_elem.appendChild(li);
        }
      else
        new_elem.appendChild(sel_frag);
      range.insertNode(new_elem);

      range.detach(); // Clean-up!
      }
    else
      {
      if (!range.text.length)
        return;
      cls = (stle[2] == '') ? '' : ' class="' + stle[2] + '"';
      html = range.htmlText;
      if (stle[1] == 'ul' || stle[1] == 'ol') // Creating lists require list elements too!
        html = '<li>' + range.htmlText + '</li>';
      new_elem = '<' + stle[1] + cls + '>' + html + '</' + stle[1] + '>';
      range.pasteHTML(new_elem);
      }

    leet.focus();
    },

  /* Color formating */

  formatColor: function(color)
    {
    var leet = leetitor,
      cmd = leet.color_btn.id;
    if (cmd == 'hilitecolor' && document.all)
      cmd = 'backcolor';
    leet.doc.execCommand(cmd, false, color); // Format
    leet.color(null, null); // To hide palette
    },

  /* Show / hide color palette */

  color: function(cmd, targ)
    {
    var leet = leetitor;

    if (leet.color_btn != null)
      leet.color_btn.className = leet.color_btn.className.replace('active', '');

    if (cmd == null)
      {
      elem('colorpalette').style.display = 'none';
      leet.color_state = false;
      }
    else
      {
      var cls = targ.className;
      targ.className += (cls.substring(cls.length - 1, cls.length) == ' ') ? 'active' : ' active'; // IE automaticly ads space between classes
      with (elem('colorpalette').style)
        {
        top = getOffsetTop(targ) + 20 + 'px';
        left = getOffsetLeft(targ) + 'px';
        display = 'block';
        }
      leet.color_btn = targ;
      leet.color_state = true;
      leet.focus();
      }
    },

  /* Sense which styles is applied to selection */

  senseFormats: function(node)
    {
    if (leetitor.source)
      return false;

    var leet = leetitor, sel = selection(leet.win, leet.doc),
      node = (document.selection) ? sel.createRange().parentElement() : sel.focusNode.parentNode,
      btns, b, f;

    btns = document.getElementsByTagName('button');
    for (b = 0; b < btns.length; b++)
      btns[b].className = btns[b].className.replace(/active/g, '');
    if (leet.settings['style'] == 'true')
      elem('selector_style').firstChild.nodeValue = 'Stil';
    if (leet.settings['format'] == 'true')
      elem('selector_formatblock').firstChild.nodeValue = 'Format';
    if (leet.settings['font'] == 'true')
      {
      elem('selector_fontname').firstChild.nodeValue = 'Teckensnitt';
      elem('selector_fontsize').firstChild.nodeValue = 'Storlek';
      }

    while (node.nodeName.toLowerCase() != 'body' && node.nodeName != '#document')
      {
      nn = node.nodeName.toLowerCase();
      if (nn == 'strong' || nn == 'b' || node.style.fontWeight == 'bold')
        elem('bold').className += ' active';
      if (nn == 'em' || nn == 'i' || node.style.fontStyle == 'italic')
        elem('italic').className += ' active';
      if (nn == 'u' || node.style.textDecoration == 'underline')
        elem('underline').className += ' active';
      if (node.style && node.style.textAlign == 'center')
        elem('justifycenter').className += ' active';
      else if (node.style && node.style.textAlign == 'right')
        elem('justifyright').className += ' active';
      else if (node.style && node.style.textAlign == 'justify')
        elem('justifyfull').className += ' active';
      else if (node.style && node.style.textAlign == 'left')
        elem('justifyleft').className += ' active';
      if (nn == 'ul')
        elem('insertunorderedlist').className += ' active';
      else if (nn == 'ol')
        elem('insertorderedlist').className += ' active';
      if (leet.settings['style'] == 'true')
        {
        for (f = 0; f < leet.styles.length; f++)
          {
          if (leet.styles[f][1] == nn && node.className == leet.styles[f][2])
            elem('selector_style').firstChild.nodeValue = leet.styles[f][0];
          }
        }
      if (leet.settings['format'] == 'true')
        {
        for (f = 0; f < leet.formats.length; f++)
          {
          if (leet.formats[f] == nn)
            elem('selector_formatblock').firstChild.nodeValue = leet.formats_locale[f];
          }
        }
      if (leet.settings['font'] == 'true')
        {
        if (node.style && node.style.fontFamily)
          elem('selector_fontname').firstChild.nodeValue = node.style.fontFamily;
        if (nn == 'font' && node.getAttribute('face'))
          elem('selector_fontname').firstChild.nodeValue = node.getAttribute('face');
        for (var f = 0; f < leet.sizes.length; f++)
          {
          if (nn == 'font' && node.getAttribute('size') == leet.sizes[f])
            elem('selector_fontsize').firstChild.nodeValue = leet.sizes_locale[f];
          }
        }
      node = node.parentNode;
      }
    },

  /* Parse pasting text */

  paste: function()
    {
    var parse = leetitor.doc.body.innerHTML;
    parse = parse.replace(/<style.*?>(.*?)<\/style>/ig, '');
    parse = parse.replace(/<.*?Mso.*?>(.*?)<\/.*?>/ig, '$1');
    parse = parse.replace(/<br style=".*?">/ig, '<br />\n'); //<br />
    parse = parse.replace(/<(.*?) align="(.*?)">/ig, '<$1>'); // style="text-align: $2;"
    if (leetitor.settings['semantic'] == 'true')
      {
      parse = parse.replace(/<font.*?>(.*?)<\/font>/ig, '$1');
      parse = parse.replace(/<span style="font-weight: bold;">(.*?)<\/span>/ig, '<strong>$1</strong>');
      parse = parse.replace(/<span style="font-style: italic;">(.*?)<\/span>/ig, '<em>$1</em>');
      parse = parse.replace(/<img(.*?)>/ig, '<img$1 />');
      }
    return parse;
    },

  /* Fix mark up */

  fixMarkup: function(parse)
    {
    parse = parse.replace(/<br>/ig, '<br />\n');
    parse = parse.replace(/(\w+)(=+)(\w+)(\s+|\/|>)+/g, '$1$2"$3"$4');
    parse = parse.replace(/<\/?(\w+)((?:[^'">]*|'[^']*'|"[^"]*")*)>/g, tagToLower);

    function tagToLower(tag_body, tag_name, tag_attr)
      {
      tag_name = tag_name.toLowerCase(); 
      var closing_tag = (tag_body.match(/^<\//));
      if (closing_tag)
        tag_body = '</' + tag_name + '>';
      else
        tag_body = '<' + tag_name + tag_attr + '>';
      return tag_body;
      }

    return parse;
    },

  /* Show / hide dropdown menu */

  dropdown: function(cmd, targ)
    {
    var leet = leetitor;

    if (leet.dropmenu != null)
      elem('menu_' + leet.dropcmd).style.display = 'none'; // Hide previous opened menu

    if (cmd != null) // Show menu if cmd is set
      {
      var cls = targ.className;
      elem('menu_' + cmd).style.display = 'block';
      leet.dropmenu = targ;
      leet.dropcmd = cmd;
      }
    else
      {
      leet.dropmenu = null;
      leet.dropcmd = null;
      }
    },

  /* Show / hide editor source */

  switchSource: function()
    {
    var leet = leetitor, source_html, editor_html;

    if (leet.source)
      {
      try
        {
        source_html = leet.doc.createRange();
        source_html.selectNodeContents(leet.doc.body);
        }
      catch (e)
        {
        source_html = leet.doc.body.innerText;
        }
      leet.doc.body.innerHTML = source_html.toString();
      leet.source = false;
      }
    else
      {
      if (document.all)
        {
        editor_html = leet.doc.body.innerHTML;
        leet.doc.body.innerText = editor_html;
        }
      else
        {
        editor_html = document.createTextNode(leet.doc.body.innerHTML);
        leet.doc.body.innerHTML = '';
        leet.doc.body.appendChild(editor_html);
        }
      leet.source = true;
      }
    leet.focus();
    },

  /* Set focus to editor */

  focus: function()
    {
    leetitor.win.focus();
    },

  /* Open dialog window */

  openWin: function(url, name, width, height)
    {
    var left = parseInt(screen.width/2 - width/2),
      top = parseInt(screen.height/2 - height/2);

    return window.open(url, name, 'left=' + left + ',top=' + top + ',height=' + height + ',width=' + width 
      + ',dependent=yes,dialog=yes,minimizable=no,scrollbars=yes');
    }
  };

addEvent(window, 'load', leetitor.init);