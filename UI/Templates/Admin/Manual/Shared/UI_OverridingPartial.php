<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span>Tutorial - How to Override User Interface (UI)</span>
</h1>

<h3>For very beginners:</h3>
<ol>
    <li>
        Just open your plugin folder. And copy &#39;UI&#39; folder to your current theme as &#39;ExpandableFAQ_UI&#39; folder.
    </li>
    <li>
        Then open any template file you want to change, i.e.<br />
        <pre>
/wp-content/themes/&lt;MY_THEME&gt;/ExpandableFAQ_UI/Templates/Front/FAQsList.php</pre>
        and edit it however you want.
    </li>
    <li>
        Save it. That&#39;s it - all done.
    </li>
</ol>

<h3>For professionals:</h3>
<ol>
    <li>
        To maintain the maximum compatibility with the future plugin updates, you should never copy all &#39;UI&#39; folder sub-folders.
        Instead of that you should copy and change only those exact folders/files which you want to override.
        It is recommended to start by copying &#39;UI&#39;' folder to your theme as &#39;ExpandableFAQ_UI&#39;' folder,
        but without it&#39;s &#39;SQL&#39; sub-folder, unless you want to change install, reset &amp; import demo SQL&#39;s as well.<br />
        <br />
        Examples:<br />
        <ul>
            <li>
                Copy <strong>template</strong> file from:
                <pre>
/wp-content/plugins/ExpandableFAQ/UI/Templates/Front/FAQsList.php</pre>
                To:<br />
                <pre>
/wp-content/themes/&lt;MY_THEME&gt;/ExpandableFAQ_UI/CarRental/Templates/Front/FAQsList.php</pre>
                And then edit the copied file however you want.
            </li>
            <li>
                Copy these three <strong>style-sheet</strong> files from:
                <pre>
/wp-content/plugins/ExpandableFAQ/UI/Assets/Front/CSS/Local/Shared/CrimsonRedColorsPartial.css
/wp-content/plugins/ExpandableFAQ/UI/Assets/Front/CSS/Local/Shared/LayoutPartial.css
/wp-content/plugins/ExpandableFAQ/UI/Assets/Front/CSS/Local/CrimsonRed.css</pre>
                To:<br />
                <pre>
/wp-content/themes/&lt;MY_THEME&gt;/ExpandableFAQ_UI/Assets/Front/CSS/Local/Shared/CrimsonRedColorsPartial.css
/wp-content/themes/&lt;MY_THEME&gt;/ExpandableFAQ_UI/Assets/Front/CSS/Local/Shared/LayoutPartial.css
/wp-content/themes/&lt;MY_THEME&gt;/ExpandableFAQ_UI/Assets/Front/CSS/Local/CrimsonRed.css</pre>
                And then edit the copied files however you want.
            </li>
        </ul>
    </li>
    <li>
        Save all your edits. That&#39;s it - all done.
    </li>
</ol>