<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span><?=esc_html($lang['LANG_MANUAL_URL_PARAMETERS_AND_HASHTAGS_TEXT']);?></span>
</h1>
<p>
    For some particular situations, instead of using shortcodes and creating a different WordPress page for each shortcode,
    you may want to use URL parameter, i.e. to focus on specific F.A.Q.&#39;s question.
</p>
<p>
    <strong>All supported URL parameters:</strong>
</p>
<ul>
    <li>
        expanded_faq=[X] - where [X] is your F.A.Q. id, taken from Expandable FAQ -&gt; FAQ Manager -&gt; F.A.Q.&#39;s
    </li>
</ul>
<p>
    <strong>All supported URL hashtags:</strong>
</p>
<ul>
    <li>
        #faq-[X] - where [X] is your F.A.Q. id, taken from Expandable FAQ -&gt; FAQ Manager -&gt; F.A.Q.&#39;s
    </li>
</ul>

<p>Please keep in mind that:</p>
<ol>
    <li>URL parameters can be send via $_GET only.</li>
    <li>Shortcode attributes has higher priority over URL parameters, so URL parameter will only work if that specific
        shortcode attribute is not used for that shortcode, or that specific shortcode attribute
        is set to &#39;-1&#39; (all).</li>
</ol>

<h3>Example:</h3>
<p>To expand a F.A.Q. with ID=1 and focus screen to it, go to &#39;https://your-site.com/faqs/?expanded_faq=1#faq-1&#39; URL.</p>