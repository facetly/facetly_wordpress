Facetly Wordpress
=================

Install Facetly Plugin
----------------------

How to install Facetly Plugin in wordpress

1. Before Installing Facetly Plugin, make sure you already have these requirements:
    
     a. Any FTP program, such as WinSCP, FileZilla, etc.
    
     b. wordpress 3.0 or higher (we are not guarantee facetly plugin would be work properly in previous version)
    
     c. Activated Wordpress E-Commerce depends on your platform, please download this plugin here (http://wordpress.org/extend/plugins/wp-e-commerce/).

     d. Add permalinks in your wordpress, please follow this guide (http://codex.wordpress.org/Settings_Permalinks_Screen).

2. Download Facetly Plugin from github (https://github.com/facetly/facetly_wordpress) and rename folder into facetly then upload it to wordpress >> wp-content >> plugins using FTP program

3. After Facetly Plugin successfully installed in your Wordpress, you will find Facetly Settings in your admin menu and contain submenus: Facetly Configuration, Fields, Reindex, and Template

Configure Facetly Plugin
------------------------

The next step is set up Facetly Plugin for your store.

1. Input your Consumer Key, Consumer Secret, Server Name, Search Limit, and Additional Variable in Facetly Configuration sub menu.

2. Check your permission for template folder and current active template folder. If the permission is "0777" (rwxrwxrwx), you can just use Copy File feature, otherwise extract facetly-search-template.zip to your current active template folder and make sure you have already backup your "searchform.php" file in your active theme folder if file exists.

    <b>Please note: this search template is based on twenty ten default theme, if you use another theme, please make some adjustments such as id and class which match your template configuration.</b>

3. After completed step 1 and 2, go to Fields sub menu. This sub menu is used to map our defined field in http://www.facetly.com and field which defined in your Wordpress store. Please follow instruction in https://www.facetly.com/doc/field to set field mapping.

4. Next step is go to Reindex sub menu. This sub menu is used to save all your product data to our server, which will used as your search data. Click Start Reindex to start the process.

    <b>Please note: you should wait until process is complete and not move to other page, otherwise you must restart your reindex process.</b>

5. Setting template for your search page. Go to Template sub menu and you will see search template and facet template. You can find more details about Template in https://www.facetly.com/doc/template

6. You can use shortcode [facetly_search output=op] to display search results or facet results in your template, where op = results to display search results or facets to display facets

7. Then you can find product with your search textbox to use this plugin
