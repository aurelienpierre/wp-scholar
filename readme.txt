=== Writer Pro Scholar ===
Plugin Name:       Writer Pro Scholar
Plugin URI:        https://eng.aurelienpierre.com/wp-scholar
Contributors:      aurelienpierre
Donate link:       https://liberapay.com/aurelienpierre/donate
Tags:              markdown, latex, table of content, footnotes, plotly, bokeh, jupyter notebook, prism, mermaid, code highlighting, charts, plots
Description:       Efficient Markdown typing with maths, footnotes and charts support for technical writers.
Version:           0.1
Requires at least: 5.2
Requires PHP:      7.2
Author:            Aurélien PIERRE
Author URI:        https://aurelienpierre.com
License:           GPL-3.0
License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
Stable tag :       0.1
Text Domain:       wp-scholar
Domain Path:       /languages

WP Scholar provides a clutter-free, direct and fast typing experience for the heavy-duty writers who type a lot every day : technical writers, engineers, analysts, journalists, researchers, scientists, etc.
It supports equations, footnotes, table of contents, code highlighting, charts drawing, interactive plots and Jupyter notebooks includes, through a direct access to the Markdown and LaTeX code.
Then, it refines the typography by converting arrows, quotes and fractions to proper HTML entities, and inserts unbreakable and thin spaces where they belong.

If you are a technical writer, you may feel WordPress Gutenberg editor is going backwards, in an MS Office direction : giving an overwhelming set of formatting options while making actual **typing** and content production really hard.
Those blocks ask you to think about the layout even before having typed the content, and make you travel constantly between mouse and keyboard. More than a hassle, they put pain on your wrists and elbows.

The truth is, WordPress is turning into a better place for marketers, but is becoming a nightmare for anyone who produces content rather than cute formatting.
While most technical writers have now turned toward static content generators (Hugo, Jekyll, etc.), and researchers keep writing fixed-width PDF papers (2021 much…),
WP Scholar tries to make WordPress great again for you, writer.

== Description ==

= Features =

1. **Newbie features** :
  * adds support for extended [Github-flavored Markdown](https://guides.github.com/features/mastering-markdown/) formatting in posts, pages and comments,
  * adds support for footnotes and inline hover cards for their content,
  * adds table of contents in posts and in sidebars with a widget,
  * adds internal anchors and backlinks to headers, for quick intra-page navigation and permanent linking to inner sections,
  * enforces correct typographic rules for the current language, such as unbreakable and thin spaces where they belong, fractions, quotes, etc.
2. **Intermediate features** :
  * adds support for [Mermaid](https://mermaid-js.github.io/mermaid/) graphs, allowing to quickly plot charts from simplified code,
  * removes WordPress markup filtering in posts and pages, allowing scripts and advanced HTML,
  * removes media library uploads limitations, allowing SVG, HTML and JSON uploads to media library, provides a shortcode to include them in posts and pages,
  * adds support for [Plotly](https://plotly.com/) and [Bokeh](https://bokeh.org/) interactive and self-hosted graphs, for beautiful data visualisations and analytics.
3. **Expert features** :
  * adds support for [LaTeX equations](https://www.mathjax.org/) in posts, comments and pages, with environments, references and equations numbering,
  * adds [syntax highlighting](https://prismjs.com/) on code blocks declared with a programming language (245 languages supported),
  * imports [Jupyter notebooks](https://jupyter.org/) and include them in pages (you can even write full posts with graphs and code in Jupyter then include them).

= Compatibility =

1. Supports [Typora](https://typora.io/) Markdown editor output and 90 % of its features, so you can type your posts off-line in Typora editor, and copy-paste the Markdown code,
1. Supports TinyMCE (classic WordPress editor) and Gutenberg in their code editor versions, plus Gutenberg HTML block. The visual editors are not fully supported ([see why](https://eng.aurelienpierre.com/wp-scholar/#Dynamic-workflow)),
1. Supports Jetpack LaTeX syntax as well as `[latex]` shortcodes,
1. Supports `[toc]` shortcode that is also used by many table of content plugins (however, they cannot be enabled at the same time),
1. Supports all shortcodes from WordPress and other plugins,
1. Saves posts as true Markdown code, so they can be edited again as Markdown (most editors convert Markdown to HTML in browser and save HTML).

= Performance =

1. WP Scholar only loads a minified 1.6 kB CSS file on every page.
1. The ressources to load (CSS and JS) are detected on a page-wise basis, so only the necessary ones are loaded and your home page doesn't get bloated with useless ressources.
1. Javascript files (Mathjax, Prism, Mermaid, Plotly, Bokeh, etc.) are loaded from `cdnjs.cloudflare.com`. This ensures fast loading and proper caching for any user.
1. WP Scholar is compatible with caching plugins and tested with [WP Rocket](https://wp-rocket.me/) ([see the configuration help](https://eng.aurelienpierre.com/wp-scholar/#Caching-settings)).
1. Most scripts are loaded in footer and can be loaded asynchronously for better loading time.
1. Since WP Scholar parse Markdown at page-rendering time, this can prove heavy and slow. A [caching plugin](https://wordpress.org/plugins/search/page+cache/) is strongly recommended.

= Demos =

The following demos are real-life posts for which this plugin was initially created :

* [WP Scholar documentation](https://eng.aurelienpierre.com/wp-rocket) is actually written with WP Scholar,
* [A post written in Markdown with Typora and copy-pasted in TinyMCE code editor](https://eng.aurelienpierre.com/2021/03/rotation-invariant-laplacian-for-2d-grids/), using maths equations, footnotes, TOC and Mermaid graphs,
* [A post written as a Jupyter notebook with Plotly interactive graphs, exported to HTML, and included in a WordPress post with the shortcode](https://eng.aurelienpierre.com/2018/05/analyse-the-heat-losses-and-design-the-heating-of-a-sprinter-van/)
* [A post written before WP Scholar in the classic TinyMCE visual editor with no Markdown but with maths, rendered through WP Scholar](https://eng.aurelienpierre.com/2019/01/derivating-hdr-ipt-direct-and-inverse-transformations/)

= Usage =

The whole thing is designed to be as lightweight and simple as possible.
There is no configuration page, just enable and start writing Markdown or LaTeX.
Everything is detected automatically, and options are defined in the text editor through HTML comments tags.


[Documentation](https://eng.aurelienpierre.com/wp-scholar/)
