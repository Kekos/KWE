/* Leetitor - Rich text editor */
/* Copyright, Christoffer Lindahl, 2008 */

/* Please adjust these settings so that Leetitor suits your web page and language */
/* OBS! File format is UTF-8 */

// Format blocks (like <p> and <h1>) and the respective name in your language

leetitor.formats = ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'pre'];
leetitor.formats_locale = ['Normal', 'Rubrik 1', 'Rubrik 2', 'Rubrik 3', 'Rubrik 4', 'Rubrik 5', 'Rubrik 6', 'Stycke', 'Förformaterad'];

// Fonts

leetitor.fonts = ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Tahoma', 'Times New Roman', 'Trebuchet MS', 'Verdana'];

// Styles
// Format: [native_lang_name, html_tag, css_class]

leetitor.styles = [['Kodrad', 'code', ''], ['Citat', 'blockquote', ''], ['Förkortning', 'abbr', ''],
  ['Akronym', 'acronym', ''], ['Upphöjt', 'sup', ''], ['Nersänkt', 'sub', '']];

/* Language settings */

leetitor.lang = [];
leetitor.lang['err_save_source'] = 'Du kan inte spara när du visar källan.'; // You can't save when the source is being displayed.
leetitor.lang['err_format_support1'] = 'Formateringskommandot '; // The format command
leetitor.lang['err_format_support2'] = ' stöds inte i din webbläsare.'; // was not supported in your browser.
leetitor.lang['err_number_format'] = 'Du måste ange ett tal.'; // You must enter a number