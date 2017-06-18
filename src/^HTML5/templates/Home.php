<?php echo $View->render('header'); ?>
    <h2>Graphite</h2>
    <p>Graphite is a Simple MVC web-application framework</p>

    <h2>Components</h2>
    <ul>
        <li>/^/ : Core files, includes base classes for Models, Controllers, a Dispatcher, and a View helper</li>
        <li>/^HTML5/ : HTML5 default site, contains HTML5/JS/CSS for basic functionality</li>
        <li>/^CLI/ : A Simple Command Line Interface, other components can expose commands to this</li>
    </ul>
    <p>It is possible to build an application on Graphite using only the core files (in path /^/).
        The other components are optional, existing as generic implementations of common functionality,
        such as user/role administration.</p>

    <h2>Installation</h2>
    <ol>
        <li>Copy the core directory (<code>/^</code>), <code>index.php</code>, and any of the optional components into
            the root of your webspace.
        </li>
        <li>Copy the <code>.htaccess</code> file into the root of your webspace, or its contents into your apache config
            (in an appropriate
            <code>&lt;directory&gt;</code> section).
        </li>
        <li>Restart apache.</li>
        <li>Visit the domain in your browser and the installer action should come up by default.</li>
    </ol>

    <h2>Created By</h2>
    <p>LoneFry<br />
        dev<script type="text/javascript">document.write('@');</script>lonefry.com
    </p>

    <h2>License</h2>
    <p>CC BY-NC-SA<br />
        Creative Commons Attribution-NonCommercial-ShareAlike<br />
        <a href="http://creativecommons.org/licenses/by-nc-sa/3.0/">http://creativecommons.org/licenses/by-nc-sa/3.0/</a>
    </p>

<?php echo $View->render('footer');
