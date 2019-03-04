#######################################################

 Magic Zoom Plus™
 OpenCart module version v4.4.13 [v1.6.71:v5.2.5]
 
 www.magictoolbox.com
 support@magictoolbox.com

 Copyright 2018 Magic Toolbox

#######################################################

INSTALLATION:

Before you start, we recommend you open readme.txt and follow those instructions. It is faster and easier than these readme_manual.txt instructions. If you use AyelShop, AceShop and MijoShop or if installation failed using the readme.txt procedure, please continue with these instructions below:

1. Copy the 'admin' and 'catalog' folders to your OpenCart directory, keeping the file structure.

2. Backup your /catalog/controller/product/product.php file and open it in a text editor (e.g. Notepad).

3. Find the line that looks like '<?php' and insert after it:

    global $aFolder;
    if (!defined('HTTP_ADMIN')) define('HTTP_ADMIN','admin');
    $aFolder = preg_replace('/.*\/([^\/].*)\//is','$1',HTTP_ADMIN);
    if (!isset($GLOBALS['magictoolbox']['magiczoomplus']) && !isset($GLOBALS['magiczoomplus_module_loaded'])) {
	include (preg_match("/components\/com_(ayelshop|aceshop|mijoshop)\/opencart\//ims",__FILE__,$matches)?'components/com_'.$matches[1].'/opencart/':'').$aFolder.'/controller/module/magiczoomplus-opencart-module/module.php';
    };

4. If your version of OpenCart is lower than '1.5.0', find the first line looking like '$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));'.

5. Replace that code with the following:

    $this->response->setOutput(magiczoomplus($this->render(TRUE),$this,'product',$product_info), $this->config->get('config_compression'));

6. If your version of OpenCart is greater than '1.5.0', find the first line looking like '$this->response->setOutput($this->render());'.

7. Replace that code with the following:

    $this->response->setOutput(magiczoomplus($this->render(TRUE),$this,'product',$product_info), $this->config->get('config_compression'));

8. Find the line that looks like '$results = $this->model_catalog_product->getProductImages($this->request->get['product_id']);'.

9. Insert the following line after it:

    $product_info['images'] = $results;

10. Backup your /catalog/controller/product/category.php file and open it in your text editor.

11. Find the line that looks like '<?php' and insert after it:

    global $aFolder;
    if (!defined('HTTP_ADMIN')) define('HTTP_ADMIN','admin');
    $aFolder = preg_replace('/.*\/([^\/].*)\//is','$1',HTTP_ADMIN);
    if (!isset($GLOBALS['magictoolbox']['magiczoomplus']) && !isset($GLOBALS['magiczoomplus_module_loaded'])) {
	include (preg_match("/components\/com_(ayelshop|aceshop|mijoshop)\/opencart\//ims",__FILE__,$matches)?'components/com_'.$matches[1].'/opencart/':'').$aFolder.'/controller/module/magiczoomplus-opencart-module/module.php';
    };

12. If your version of OpenCart is lower than '1.5.0', find the line looking like '$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));'.

13. Replace that code with the following:

    $this->response->setOutput(magiczoomplus($this->render(TRUE),$this,'category', $results), $this->config->get('config_compression'));

14. If your version of OpenCart is greater than '1.5.0', find the line looking like '$this->response->setOutput($this->render());'.

15. Replace that code with the following:

    $this->response->setOutput(magiczoomplus($this->render(TRUE),$this,'category', $results), $this->config->get('config_compression'));

16. Backup your /catalog/controller/product/manufacturer.php file and open it in your text editor.

17. Find the line that looks like '<?php' and insert after it:

    global $aFolder;
    if (!defined('HTTP_ADMIN')) define('HTTP_ADMIN','admin');
    $aFolder = preg_replace('/.*\/([^\/].*)\//is','$1',HTTP_ADMIN);
    if (!isset($GLOBALS['magictoolbox']['magiczoomplus']) && !isset($GLOBALS['magiczoomplus_module_loaded'])) {
	include (preg_match("/components\/com_(ayelshop|aceshop|mijoshop)\/opencart\//ims",__FILE__,$matches)?'components/com_'.$matches[1].'/opencart/':'').$aFolder.'/controller/module/magiczoomplus-opencart-module/module.php';
    };

18. If your version of OpenCart is lower than '1.5.0', find the second line that's look like '$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));'.

19. Replace that code with the following:

    $this->response->setOutput(magiczoomplus($this->render(TRUE),$this,'manufacturers', $results), $this->config->get('config_compression'));

20. If your version of OpenCart is greater than '1.5.0', find the second line that's look like '$this->response->setOutput($this->render());'.

21. Replace that code with the following:

    $this->response->setOutput(magiczoomplus($this->render(TRUE),$this,'manufacturers', $results), $this->config->get('config_compression'));

22. Backup your /catalog/controller/product/search.php file and open it in your text editor.

23. Find the line that looks like '<?php' and insert after it:

    global $aFolder;
    if (!defined('HTTP_ADMIN')) define('HTTP_ADMIN','admin');
    $aFolder = preg_replace('/.*\/([^\/].*)\//is','$1',HTTP_ADMIN);
    if (!isset($GLOBALS['magictoolbox']['magiczoomplus']) && !isset($GLOBALS['magiczoomplus_module_loaded'])) {
    include (preg_match("/components\/com_(ayelshop|aceshop|mijoshop)\/opencart\//ims",__FILE__,$matches)?'components/com_'.$matches[1].'/opencart/':'').$aFolder.'/controller/module/magiczoomplus-opencart-module/module.php';
    };

24. If your version of OpenCart is lower than '1.5.0', find the second line that's look like '$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));'.

25. Replace that code with the following:

    $this->response->setOutput(magiczoomplus($this->render(TRUE),$this,'search', (isset($results) ? $results : array() )), $this->config->get('config_compression'));

26. If your version of OpenCart is greater than '1.5.0', find the second line that's look like '$this->response->setOutput($this->render());'.

27. Replace that code with the following:

    $this->response->setOutput(magiczoomplus($this->render(TRUE),$this,'search', (isset($results) ? $results : array() )), $this->config->get('config_compression'));

28. Backup your /catalog/controller/common/home.php file and open it in editor.

29. If your version of OpenCart is lower than '1.5.0', find the line looking like '$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));'.

30. Replace that code with the following:

    $this->render();
    if(version_compare(VERSION, '1.4.9', '<')) {
        $this->output = magiczoomplus($this->output,$this,'latest_home_category',$this->model_catalog_product->getLatestProducts(8));
    }
    $this->response->setOutput($this->output, $this->config->get('config_compression'));

31. If your version of OpenCart is greater than '1.5.0', find the line looking like '$this->response->setOutput($this->render());'.

32. Replace that code with the following:

    $this->render();
    if(version_compare(VERSION, '1.4.9', '<')) {
        $this->output = magiczoomplus($this->output,$this,'latest_home_category',$this->model_catalog_product->getLatestProducts(8));
    }
    $this->response->setOutput($this->output, $this->config->get('config_compression'));

33. Backup your /catalog/controller/common/header.php file and open it in your text editor.

34. Find the line that looks like '<?php' and insert after it:

    global $aFolder;
    if (!defined('HTTP_ADMIN')) define('HTTP_ADMIN','admin');
    $aFolder = preg_replace('/.*\/([^\/].*)\//is','$1',HTTP_ADMIN);
    if (!isset($GLOBALS['magictoolbox']['magiczoomplus']) && !isset($GLOBALS['magiczoomplus_module_loaded'])) {
	include (preg_match("/components\/com_(ayelshop|aceshop|mijoshop)\/opencart\//ims",__FILE__,$matches)?'components/com_'.$matches[1].'/opencart/':'').$aFolder.'/controller/module/magiczoomplus-opencart-module/module.php';
    };

35. Find the line '$this->render();'.

36. Replace that code with the following:

    $this->render();
    if($this->config->get('magiczoomplus_status') != 0) {
        $tool = magiczoomplus_load_core_class($this);
        if(use_effect_on($tool)) {
            $this->output = set_headers($this->output);
        }
    };

37. Backup your /catalog/controller/module/latest.php file and open it in your text editor.

38. Find the line that looks like '<?php' and insert after it:

    global $aFolder;
    if (!defined('HTTP_ADMIN')) define('HTTP_ADMIN','admin');
    $aFolder = preg_replace('/.*\/([^\/].*)\//is','$1',HTTP_ADMIN);
    if (!isset($GLOBALS['magictoolbox']['magiczoomplus']) && !isset($GLOBALS['magiczoomplus_module_loaded'])) {
	include (preg_match("/components\/com_(ayelshop|aceshop|mijoshop)\/opencart\//ims",__FILE__,$matches)?'components/com_'.$matches[1].'/opencart/':'').$aFolder.'/controller/module/magiczoomplus-opencart-module/module.php';
    };

39. Find the line that looks like '$this->render();'

40. Replace that code with the following:

    global $aFolder; include($aFolder.'/controller/module/magiczoomplus-opencart-module/boxes.inc');

41. Repeat the modifications you made to 'latest.php' to these files:

    /catalog/controller/module/bestseller.php
    /catalog/controller/module/special.php
    /catalog/controller/module/featured.php

42. Open /catalog/controller/module/featured.php again and find the line like '$product_info = $this->model_catalog_product->getProduct($product_id);'

43. Add the following code after it :

    $product_infos[] = $product_info;

44. You are done! Now you can open the 'Extensions' page in your OpenCart admin panel to activate and customize the module.

