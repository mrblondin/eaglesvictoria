<?php
    /**
     *  OpenCart module installer class
     */

    require_once(dirname(__FILE__) . '/magictoolbox.installer.core.class.php');
    include(dirname(__FILE__).'/../config.php');
    $root_dir = dirname(__FILE__).'/../';
    if (defined("HTTP_SERVER") && constant("HTTP_SERVER") && defined("HTTP_ADMIN") && constant("HTTP_ADMIN")) {
        $admin_folder_name = str_replace('/','',(str_replace(HTTP_SERVER,'',HTTP_ADMIN)));
    } else { //opencart 2 ?
    	$folder_contents = scandir($root_dir);
    	if (!(in_array('admin', $folder_contents) && file_exists($root_dir.'admin/config.php'))) {
			foreach ($folder_contents as $value) {
				if (is_dir($root_dir.$value) && $value != '.' && $value != '..'){
					if (file_exists($root_dir.$value.'/config.php')) {
						$admin_folder_name = $value;
						continue;
					}
				}
			}
		}
		if (!isset($admin_folder_name)) $admin_folder_name = 'admin';
	
    }

    class MagicToolboxOpencartModuleInstallerClass extends MagicToolboxCoreInstallerClass {

        public $wearenotalone = false;
        public $version;
        public $modulesPath;
        public $englishPath;

        function __construct() {
            global $admin_folder_name;

            $this->logEnabled = false;
            $this->dir = dirname(dirname(__FILE__));
            $this->modDir = dirname(__FILE__) . '/module';
            $this->resDir = preg_replace('/^(.*?\/)[^\/]+\/[^\/]+$/is', '$1', $_SERVER['SCRIPT_NAME']) . 'catalog/view/css';
            $this->version = $this->getPlatformVersion();
            $this->getModulePath();
        }

         
        function vqmodCheck() {
            global $admin_folder_name;

            if(file_exists($this->dir.'/index.php')) {

		$contents = file_get_contents($this->dir.'/index.php');
		if(preg_match('/VirtualQMOD/is', $contents)) {
		    return true;
		}
	    }
	    return false;
        }
        
        function isModuleInstalled() {
            global $admin_folder_name;

            $this->setStatus('check', 'module');

            if(file_exists($this->dir.'/'.$admin_folder_name.'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/magiczoomplus.module.core.class.php')) {

                if('magiczoomplus' == 'magicscroll') {
                    $contents = file_get_contents($this->dir.'/admin/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/magicscroll.module.core.class.php');
                    if(!preg_match('/<!-- Magic Scroll OpenCart module/', $contents)) {
                        return false;
                    }
                }
                return true;
            }
            return false;
        }

        function checkPlace() {
            $this->setStatus('check', 'place');
             if(!file_exists($this->dir . '/system/startup.php')) {
                $this->setError('Wrong location: please upload the files from the ZIP archive to the OpenCart store directory.');
                return false;
            }
            return true;
        }

        function checkPerm() {
            $this->setStatus('check', 'perm');
            global $admin_folder_name;

            $files = array( //basic files and dirs
                '/'.$admin_folder_name.'/controller/'.$this->modulesPath.'/',
                '/'.$admin_folder_name.'/view/template/'.$this->modulesPath.'/',
                '/'.$admin_folder_name.'/view/image/',
                '/catalog/view/',
                
            );
            
            if (!$this->vqmodCheck() && version_compare($this->version,'2','<')) { //only for 1.5x and for none-vqmod
		$additional_files = array (
		    '/catalog/controller/product',
		    '/catalog/controller/'.$this->modulesPath.'/',
		    '/catalog/controller/common',
		    '/catalog/controller/common/column_left.php',
		    '/catalog/controller/common/column_right.php',
		    '/catalog/controller/common/content_top.php',
		    '/catalog/controller/common/content_bottom.php',
		    '/catalog/controller/product/product.php',
		    '/catalog/controller/product/category.php',
		    '/catalog/controller/product/manufacturer.php',
		    '/catalog/controller/product/search.php',
		    '/catalog/controller/common/home.php',
		    '/catalog/controller/common/header.php',
		    '/catalog/controller/'.$this->modulesPath.'/latest.php',
		    '/catalog/controller/'.$this->modulesPath.'/bestseller.php',
		    '/catalog/controller/'.$this->modulesPath.'/special.php',
		    '/catalog/controller/'.$this->modulesPath.'/featured.php'
		);
		$files = array_merge($files,$additional_files);
		/*vqmod fix start*/
		$files = $this->vqmod_fix($files);
		/*vqmod fix end*/
            }
            
            $lang_dirs = array();
            $directories = glob($this->dir . '/'.$admin_folder_name.'/language/*' , GLOB_ONLYDIR);
            foreach ($directories as $ldir) {
                $ldir = preg_replace('/^.*\/([a-zA-Z\-\_]+$)/is','$1',$ldir);
                $files[] = '/'.$admin_folder_name.'/language/'.$ldir;
            }

            list($result, $wrang) = $this->checkFilesPerm($files);
            if(!$result) {
                $this->setError('This installer need to modify some OpenCart store files.');
                $this->setError('Please check write access for following files of your OpenCart store:');
                $this->setError($wrang, '&nbsp;&nbsp;&nbsp;-&nbsp;');
                return false;
            }
            return true;
        }

        function backupFiles() {
        
	    if (($this->vqmodCheck() && version_compare($this->version,'2','<')) || version_compare($this->version,'2','>=')) return true;
	    
            $this->setStatus('backup', 'files');
            
            $backups = array(
		'/catalog/controller/common/column_left.php',
                '/catalog/controller/common/column_right.php',
                '/catalog/controller/common/content_top.php',
                '/catalog/controller/common/content_bottom.php',
                '/catalog/controller/product/product.php',
                '/catalog/controller/product/category.php',
                '/catalog/controller/product/manufacturer.php',
                '/catalog/controller/product/search.php',
                '/catalog/controller/common/home.php',
                '/catalog/controller/common/header.php',
                '/catalog/controller/'.$this->modulesPath.'/latest.php',
                '/catalog/controller/'.$this->modulesPath.'/bestseller.php',
                '/catalog/controller/'.$this->modulesPath.'/special.php',
                '/catalog/controller/'.$this->modulesPath.'/featured.php'
            );
            

            if ($this->vqmodCheck()) return true; 
            
            /*vqmod fix start*/
            $backups = $this->vqmod_fix($backups);
            /*vqmod fix end*/
            if (!$this->multiply_check($backups)) {// do not backup files that were already modified
                $this->wearenotalone = true;
                return true; 
            }

            list($result, $wrang) = $this->createBackups($backups);
            if(!$result) {
                $this->setError('Can\'t create backups for following files:');
                $this->setError($wrang, '&nbsp;&nbsp;&nbsp;-&nbsp;');
                $this->setError('Please check write access');
                return false;
            }
            return true;
        }

        function restoreStep_backupFiles() {
            
	    if (($this->vqmodCheck() && version_compare($this->version,'2','<')) || version_compare($this->version,'2','>=')) return true;
        
            $backups = array(
		'/catalog/controller/common/column_left.php',
                '/catalog/controller/common/column_right.php',
                '/catalog/controller/common/content_top.php',
                '/catalog/controller/common/content_bottom.php',
                '/catalog/controller/product/product.php',
                '/catalog/controller/product/category.php',
                '/catalog/controller/product/manufacturer.php',
                '/catalog/controller/product/search.php',
                '/catalog/controller/common/home.php',
                '/catalog/controller/common/header.php',
                '/catalog/controller/'.$this->modulesPath.'/latest.php',
                '/catalog/controller/'.$this->modulesPath.'/bestseller.php',
                '/catalog/controller/'.$this->modulesPath.'/special.php',
                '/catalog/controller/'.$this->modulesPath.'/featured.php'
            );

            if ($this->vqmodCheck()) return true; 
            
            /*vqmod fix start*/
            $backups = $this->vqmod_fix($backups);
            /*vqmod fix end*/

            /* do not restore files that were already modified*/
            $test = file_get_contents($this->dir.'/catalog/controller/'.$this->modulesPath.'/featured.php');
            $test_matches = substr_count($test,'boxes.inc');
            if ($test_matches > 0) return true;

            $this->restoreFromBackups($backups);
            $this->removeBackups($backups);

        }

        function installFiles() {
            $this->setStatus('install', 'files');
            global $admin_folder_name;

            // copy folders
            //if (version_compare($this->version,'2.3','>=')) {
            
                $this->copyDir($this->modDir . '/admin/controller/module/', $this->dir . '/'.$admin_folder_name.'/controller/'.$this->modulesPath.'/');
                $this->copyDir($this->modDir . '/admin/language/english/module/', $this->dir . '/'.$admin_folder_name.'/language/'.$this->englishPath.'/'.$this->modulesPath.'/');
                $this->copyDir($this->modDir . '/admin/view/image/', $this->dir . '/'.$admin_folder_name.'/view/image/');
                $this->copyDir($this->modDir . '/admin/view/template/module/', $this->dir . '/'.$admin_folder_name.'/view/template/'.$this->modulesPath.'/');
                
                if ('magiczoomplus' == 'magic360') { //copy gallery files
                    $this->copyDir($this->modDir . '/admin/controller/catalog/', $this->dir . '/'.$admin_folder_name.'/controller/catalog/');
                    $this->copyDir($this->modDir . '/admin/language/english/catalog/', $this->dir . '/'.$admin_folder_name.'/language/'.$this->englishPath.'/catalog/');
                    $this->copyDir($this->modDir . '/admin/model/catalog/', $this->dir . '/'.$admin_folder_name.'/model/catalog/');
                    $this->copyDir($this->modDir . '/admin/view/template/catalog/', $this->dir . '/'.$admin_folder_name.'/view/template/catalog/');
                    $this->copyDir($this->modDir . '/admin/view/javascript/', $this->dir . '/'.$admin_folder_name.'/view/javascript/');
                    $this->copyDir($this->modDir . '/admin/view/stylesheet/', $this->dir . '/'.$admin_folder_name.'/view/stylesheet/');
                    $this->copyFile($this->modDir . '/admin/controller/module/magictoolbox/360icon.jpg', $this->dir . '/image/magic360/360icon.jpg'); //copy spin logo
                }
                
            /*} else {
                $this->copyDir($this->modDir . '/admin', $this->dir . '/'.$admin_folder_name);
            }*/
            $this->copyDir($this->modDir . '/catalog', $this->dir . '/catalog');

            //$this->copyJsCss('install');

            $directories = glob($this->dir . '/'.$admin_folder_name.'/language/*' , GLOB_ONLYDIR);
            foreach ($directories as $ldir) {
                if (!file_exists($ldir.'/'.$this->modulesPath.'/magiczoomplus.php') && file_exists($this->dir . '/'.$admin_folder_name.'/language/'.$this->englishPath.'/module/magiczoomplus.php')) {
                    if(!is_dir($ldir.'/'.$this->modulesPath)) {
                        if(mkdir($ldir.'/'.$this->modulesPath)) {
                            $this->log('CREATE DIR '.$ldir.'/'.$this->modulesPath);
                        }
                    }
                    if(copy($this->dir.'/'.$admin_folder_name.'/language/'.$this->englishPath.'/module/magiczoomplus.php', $ldir.'/'.$this->modulesPath.'/magiczoomplus.php')) {
                        $this->log('CREATE FILE '.$ldir.'/module/magiczoomplus.php');
                    }
                }
            }

            
            if ($this->vqmodCheck() && version_compare($this->version,'2','<')) { //use vqmod
            
                $this->copyFile(dirname(__FILE__).'/module/vqmod/xml/magiczoomplus_vqmod.xml', $this->dir.'/vqmod/xml/magiczoomplus_vqmod.xml');
                if('magiczoomplus' == 'magic360'){
                    $this->copyFile(dirname(__FILE__).'/module/vqmod/xml/magic360gallery_vqmod.xml', $this->dir.'/vqmod/xml/magic360gallery_vqmod.xml');
                }
		
            } else if (version_compare($this->version,'2','>=')) { //use ocmod
            
                $this->copyFile(dirname(__FILE__).'/module/system/magiczoomplus.ocmod.xml', $this->dir.'/system/magiczoomplus.ocmod.xml');
                $this->copyFile(dirname(__FILE__).'/module/system/magictoolbox-main.ocmod.xml', $this->dir.'/system/magictoolbox-main.ocmod.xml');
                if('magiczoomplus' == 'magic360'){
                    $this->copyFile(dirname(__FILE__).'/module/system/magic360gallery.ocmod.xml', $this->dir.'/system/magic360gallery.ocmod.xml');
                }
                if(in_array('magiczoomplus',array('magiczoom','magiczoomplus','magicthumb'))) {
                    $this->copyFile(dirname(__FILE__).'/module/system/magictoolbox-video.ocmod.xml', $this->dir.'/system/magictoolbox-video.ocmod.xml');
                }
		
            } else { //use files rewrite
            
		/******************************************************************************   modify product.php ******************************************************************************/
		$c = file_get_contents($this->dir.'/catalog/controller/product/product.php');

		$pattern = 'class ControllerProductProduct extends Controller {';

		$replace = 'global $aFolder;'. "\n\t" . 
			  'if (!defined(\'HTTP_ADMIN\')) define(\'HTTP_ADMIN\',\'admin\');'. "\n\t" .
			  '$aFolder = preg_replace(\'/.*\/([^\/].*)\//is\',\'$1\',HTTP_ADMIN);'. "\n\t" .
			  'if (!isset($GLOBALS[\'magictoolbox\'][\'magiczoomplus\']) && !isset($GLOBALS[\'magiczoomplus_module_loaded\'])) {'. "\n\t" .
			      'include (preg_match("/components\/com_(ayelshop|aceshop|mijoshop)\/opencart\//ims",DIR_APPLICATION,$matches)?\'components/com_\'.$matches[1].\'/opencart/\':\'\').$aFolder.\'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/module.php\';'. "\n\t" .
			  '};'. "\n\t";

		$replace = $this->addComments($replace);
		$replace = $replace.$pattern. "\n\t";

		$c = str_replace($pattern, $replace, $c);
		
		if (!$this->wearenotalone) {
		    
		    $replace = $replace . 'foreach ($magicArray as $toolId => $run) { '. "\n\t\t\t" .'
						if ($run) { '. "\n\t\t\t" .'
						    $func = "$toolId".\'_set_headers\'; '. "\n\t\t\t" .'
						    $magicContent = $func($magicContent,$this,\'product\',$product_info); '. "\n\t\t\t" .'
						}
					    }';
		}
		

		if (!$this->wearenotalone) {
		
		    $pattern = '\/\*\s+Magictoolbox.*?BEGIN\s+\*\/.*?\$magicArray\[[^\]]*\]\s+\=\s+true;.*?\/\*\s+Magictoolbox.*?END\s+\*\/';
		    
		    $replace =  '$magicArray = array(
				  "magiczoomplus" => false,
				  "magiczoom" => false,
				  "magicthumb" => false,
				  "magictouch" => false,
				  "magic360" => false,
				  "magicmagnify" => false,
				  "magicmagnifyplus" => false,
				  "magicscroll" => false,
				  "magicslideshow" => false);' . "\n\t\t" . $replace;
		    
		    $replace = $replace . 'foreach ($magicArray as $toolId => $run) { '. "\n\t\t\t" .'
						if ($run) { '. "\n\t\t\t" .'
						    $func = "$toolId"; '. "\n\t\t\t" .'
						    $magicContent = $func($magicContent,$this,\'product\',$product_info); '. "\n\t\t\t" .'
						}
					    }';
		    
		    
		    $replace = $this->addComments($replace);
		    $replace = '$magicContent = $this->render(TRUE);' . "\n\t" . $replace;          

		    $replace = $replace . '$this->response->setOutput($magicContent, $this->config->get(\'config_compression\'));'. "\n\t";

		    $c = preg_replace('/'.$pattern.'/is', $replace, $c, 1); //only first needle replace
		}

	      /*FOR NEW OPEN CARTS*/
		if (!$this->wearenotalone) {
		    /*if (version_compare($this->version,'2','>=')) {
			$pattern = '\$this->response->setOutput\(\$this->load->view\(\$this->config->get\(\'config_template\'\) \. \'\\/template\\/product\\/product.tpl\', \$data\)\);';
		    } else {*/
			$pattern = '\$this->response->setOutput\(\$this->render\(\)\);';
		    /*}*/
		} else {
		    $pattern = '\/\*\s+Magictoolbox.*?BEGIN\s+\*\/.*?\$magicArray\[[^\]]*\]\s+\=\s+true;.*?\/\*\s+Magictoolbox.*?END\s+\*\/';
		}

		$replace = '$magicArray[\'magiczoomplus\'] = true;' . "\n\t";
		$replace = $this->addComments($replace);
		
		if (!$this->wearenotalone) {
		  $replace =  '$magicArray = array(
				  "magiczoomplus" => false,
				  "magiczoom" => false,
				  "magicthumb" => false,
				  "magictouch" => false,
				  "magic360" => false,
				  "magicmagnify" => false,
				  "magicmagnifyplus" => false,
				  "magicscroll" => false,
				  "magicslideshow" => false);' . "\n\t\t" . $replace;

		    $replace = $replace . 'foreach ($magicArray as $toolId => $run) { '. "\n\t\t\t" .'
						if ($run) { '. "\n\t\t\t" .'
						    $func = "$toolId"; '. "\n\t\t\t" .'
						    $magicContent = $func($magicContent,$this,\'product\',$product_info); '. "\n\t\t\t" .'
						}
					    }';
		    /*if ($version >= 2) {
			$replace = '$magicContent = $this->load->view($this->config->get(\'config_template\') . \'/template/product/product.tpl\', $data);' . "\n\t" . $replace;          
			$replace = $replace . '$this->response->setOutput($magicContent,$this,\'product\',$product_info);	'. "\n\t"; 
		    } else {*/
			$replace = '$magicContent = $this->render(TRUE);' . "\n\t" . $replace;          
			$replace = $replace . '$this->response->setOutput($magicContent, $this->config->get(\'config_compression\'));'. "\n\t";
		    /*}*/
		    $c = preg_replace('/'.$pattern.'/is', $replace, $c, 1); //only first needle replace
		    
		} else {
		    $c = preg_replace('/'.$pattern.'/is', '$0'.$replace, $c, 1); //only first needle replace
		}

		$pattern = '$results = $this->model_catalog_product->getProductImages($this->request->get[\'product_id\']);';
		$replace = '$results = $this->model_catalog_product->getProductImages($this->request->get[\'product_id\']) ; $product_info[\'images\'] = $results;';

		$c = str_replace($pattern, $replace, $c);

		file_put_contents($this->dir.'/catalog/controller/product/product.php', $c);
		/*vqmod fix start*/
		if ($vqName = $this->vqmod_fix('/catalog/controller/product/product.php')) {
		    file_put_contents($this->dir.$vqName, $c);
		}
		/*vqmod fix end*/

		/***************************************************** modify category.php ******************************************************************************************************/
		$c = file_get_contents($this->dir . '/catalog/controller/product/category.php');

		$pattern = 'class ControllerProductCategory extends Controller {';

		$replace = 'global $aFolder;'. "\n\t" . 
			  'if (!defined(\'HTTP_ADMIN\')) define(\'HTTP_ADMIN\',\'admin\');'. "\n\t" .
			  '$aFolder = preg_replace(\'/.*\/([^\/].*)\//is\',\'$1\',HTTP_ADMIN);'. "\n\t" .
			  'if (!isset($GLOBALS[\'magictoolbox\'][\'magiczoomplus\']) && !isset($GLOBALS[\'magiczoomplus_module_loaded\'])) {'. "\n\t" .
			  '    include (preg_match("/components\/com_(ayelshop|aceshop|mijoshop)\/opencart\//ims",DIR_APPLICATION,$matches)?\'components/com_\'.$matches[1].\'/opencart/\':\'\').$aFolder.\'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/module.php\';'. "\n\t" .
			  '};'. "\n\t";
			  
		$replace = $this->addComments($replace);
		$replace = $replace.$pattern. "\n\t";

		$c = str_replace($pattern, $replace, $c);

		if (!$this->wearenotalone) {
		
		    /*if ($version >= 2) {
			$pattern = '\$this->response->setOutput\(\$this->load->view\(\$this->config->get\(\'config_template\'\) \. \'\\/template\\/product\\/category.tpl\', \$data\)\);'; 
		    } else {*/
			$pattern = '\$this->response->setOutput\(\$this->render\(TRUE\), \$this->config->get\(\'config_compression\'\)\);';
		    /*}*/

		    $replace = '$magicContent = magiczoomplus($magicContent,$this,\'category\',$results);' . "\n\t";
		    $replace = $this->addComments($replace);
		    
		    /*if ($version >= 2) {
			$replace = '$magicContent = $this->load->view($this->config->get(\'config_template\') . \'/template/product/category.tpl\', $data);' . "\n\t" . $replace;          
		    } else {*/
			$replace = '$magicContent = $this->render(TRUE);' . "\n\t" . $replace;          
		    /*}*/

		    $replace = $replace . '$this->response->setOutput($magicContent, $this->config->get(\'config_compression\'));'. "\n\t";
		
		    $c = preg_replace('/'.$pattern.'/is', $replace, $c, 1); //only first needle replace
		}

		/*FOR NEW OPEN CARTS*/
		if (!$this->wearenotalone) {
		    /*if ($version >= 2) {
			$pattern = '\$this->response->setOutput\(\$this->load->view\(\$this->config->get\(\'config_template\'\) \. \'\\/template\\/product\\/category.tpl\', \$data\)\);'; 
		    } else {*/
			$pattern = '\$this->response->setOutput\(\$this->render\(\)\);';
		    /*}*/
		} else {
		    /*if ($version >= 2) {
			$pattern = '\$magicContent\s+\=\s+\$this->load->view\(\$this->config->get\(\'config_template\'\)\s+\.\s+\'\/template\/product\/category\.tpl\',\s+\$data\);';
		    } else {*/
			$pattern = '\$magicContent\s+\=\s+\$this->render\(TRUE\);';
		    /*}*/
		}
		
		$replace = '$magicContent = magiczoomplus($magicContent,$this,\'category\',$results);' . "\n\t";
		$replace = $this->addComments($replace);
		/*if ($version >= 2) {
		    $replace = '$magicContent = $this->load->view($this->config->get(\'config_template\') . \'/template/product/category.tpl\', $data);' . "\n\t" . $replace;          
		} else {*/
		    $replace = '$magicContent = $this->render(TRUE);' . "\n\t" . $replace;          
		/*}*/

		if (!$this->wearenotalone) {
		    $replace = $replace . '$this->response->setOutput($magicContent, $this->config->get(\'config_compression\'));'. "\n\t";
		}           

		$c = preg_replace('/'.$pattern.'/is', $replace, $c, 1); //only first needle replace

		file_put_contents($this->dir.'/catalog/controller/product/category.php', $c);
		/*vqmod fix start*/
		if ($vqName = $this->vqmod_fix('/catalog/controller/product/category.php')) {
		    file_put_contents($this->dir.$vqName, $c);
		}
		/*vqmod fix end*/

		/******************************************************************************   modify manufacturer.php ******************************************************************************/
		$c = file_get_contents($this->dir . '/catalog/controller/product/manufacturer.php');

		$pattern = 'class ControllerProductManufacturer extends Controller {';

		$replace = 'global $aFolder;'. "\n\t" . 
			  'if (!defined(\'HTTP_ADMIN\')) define(\'HTTP_ADMIN\',\'admin\');'. "\n\t" .
			  '$aFolder = preg_replace(\'/.*\/([^\/].*)\//is\',\'$1\',HTTP_ADMIN);'. "\n\t" .
			  'if (!isset($GLOBALS[\'magictoolbox\'][\'magiczoomplus\']) && !isset($GLOBALS[\'magiczoomplus_module_loaded\'])) {'. "\n\t" .
			  '    include (preg_match("/components\/com_(ayelshop|aceshop|mijoshop)\/opencart\//ims",DIR_APPLICATION,$matches)?\'components/com_\'.$matches[1].\'/opencart/\':\'\').$aFolder.\'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/module.php\';'. "\n\t" .
			  '};'. "\n\t";

		$replace = $this->addComments($replace);
		$replace = $replace.$pattern. "\n\t";

		$c = str_replace($pattern, $replace, $c);

		/*if (!$this->wearenotalone) {
		    if ($version >= 2) { 
			$pattern = '\$this->response->setOutput\(\$this->load->view\(\$this->config->get\(\'config_template\'\) \. \'\\/template\\/product\\/manufacturer_info.tpl\', \$data\)\);'; 
		    } else {
			$pattern = '\$this->response->setOutput\(\$this->render\(TRUE\), \$this->config->get\(\'config_compression\'\)\);';
		    }
		
		    //$replace = '$magicContent =magiczoomplus($this->render(TRUE),$this,\'manufacturers\', $results);' . "\n\t";
		    if ($version >= 2) { 
			$replace = '$magicContent = magiczoomplus($this->load->view($this->config->get(\'config_template\') . \'/template/product/manufacturer_info.tpl\', $data),$this,\'manufacturers\', $results);' . "\n\t";
		    } else {
			$replace = '$magicContent = magiczoomplus($this->render(TRUE),$this,\'manufacturers\', $results);' . "\n\t";
		    }
		    $replace = $this->addComments($replace);
		    
		    if ($version >= 2) { 
			$replace = '$magicContent = $this->load->view($this->config->get(\'config_template\') . \'/template/product/manufacturer_info.tpl\', $data);' . "\n\t" . $replace;          
		    } else {
			$replace = '$magicContent = $this->render(TRUE);' . "\n\t" . $replace;          
		    }

		    $replace = $replace . '$this->response->setOutput($magicContent, $this, $this->config->get(\'config_compression\'));'. "\n\t";
		    
		    $c = preg_replace('/'.$pattern.'/is', $replace, $c, 2); //only first two needles replace
		}*/
		/*FOR NEW OPEN CARTS*/

		if (!$this->wearenotalone) {
		    /*if ($version >= 2) { 
			$pattern = '\$this->response->setOutput\(\$this->load->view\(\$this->config->get\(\'config_template\'\) \. \'\\/template\\/product\\/manufacturer_info.tpl\', \$data\)\);'; 
		    } else {*/
			$pattern = '\$this->response->setOutput\(\$this->render\(\)\);';
		    /*}*/
		} else {
		    /*if ($version >= 2) { 
			$pattern = '\$magicContent\s+\=\s+\$this->load->view\(\$this->config->get\(\'config_template\'\)\s+\.\s+\'\/template\/product\/manufacturer_info\.tpl\',\s+\$data\);';
		    } else {*/
			$pattern = '\$magicContent\s+\=\s+\$this->render\(TRUE\);';
		    /*}*/
		}
		
		$replace = '$magicContent = magiczoomplus($magicContent,$this,\'manufacturers\', $results);' . "\n\t";
		
		$replace = $this->addComments($replace);
		
		/*if ($version >= 2) { 
		    $replace = '$magicContent = $this->load->view($this->config->get(\'config_template\') . \'/template/product/manufacturer_info.tpl\', $data);' . "\n\t" . $replace;          
		} else {*/
		    $replace = '$magicContent = $this->render(TRUE);' . "\n\t" . $replace;          
		/*}*/

		if (!$this->wearenotalone) {
		    $replace = $replace . '$this->response->setOutput($magicContent, $this->config->get(\'config_compression\'));'. "\n\t";
		}           

		$c = preg_replace('/'.$pattern.'/is', $replace, $c, 2); //only first two needles replace

		file_put_contents($this->dir.'/catalog/controller/product/manufacturer.php', $c);
		/*vqmod fix start*/
		if ($vqName = $this->vqmod_fix('/catalog/controller/product/manufacturer.php')) {
		    file_put_contents($this->dir.$vqName, $c);
		}
		/*vqmod fix end*/


		/************************************************************** modify search.php ************************************************************************************/
		$c = file_get_contents($this->dir . '/catalog/controller/product/search.php');

		$pattern = 'class ControllerProductSearch extends Controller {';
		$replace = 'global $aFolder;
			    if (!defined(\'HTTP_ADMIN\')) define(\'HTTP_ADMIN\',\'admin\');
			    $aFolder = preg_replace(\'/.*\/([^\/].*)\//is\',\'$1\',HTTP_ADMIN);
			    if (!isset($GLOBALS[\'magictoolbox\'][\'magiczoomplus\']) && !isset($GLOBALS[\'magiczoomplus_module_loaded\'])) {
				include (preg_match("/components\/com_(ayelshop|aceshop|mijoshop)\/opencart\//ims",DIR_APPLICATION,$matches)?\'components/com_\'.$matches[1].\'/opencart/\':\'\').$aFolder.\'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/module.php\';
			    };';
		$replace =  $this->addComments($replace);
		$replace = $replace.'class ControllerProductSearch extends Controller {';
		
		
		$c = str_replace($pattern, $replace, $c);
		
		/*if ($version < 2) { 
		    $pattern = '\$this->response->setOutput\(\$this->render\(TRUE\), \$this->config->get\(\'config_compression\'\)\);';
		    $replace = '$this->response->setOutput(magiczoomplus($this->render(TRUE),$this,\'search\', (isset($results) ? $results : array() )), $this->config->get(\'config_compression\'));';
		} else {
		    $pattern = '\$this->response->setOutput\(\$this->load->view\(\$this->config->get\(\'config_template\'\)\s+\.\s+\'\/template\/product\/search\.tpl\', \$data\)\);';
		    $replace = '$this->response->setOutput(magiczoomplus($this->load->view($this->config->get(\'config_template\') . \'/template/product/search.tpl\', $data),$this,\'search\',(isset($results) ? $results : array() )));	';
		}
		$c = preg_replace('/'.$pattern.'/is', $replace, $c, 2); //only first two needles replace*/

		/*FOR NEW OPEN CARTS*/
		
		
		
		if (!$this->wearenotalone) {
		    /*if ($version >= 2) { 
			$pattern = '\$this->response->setOutput\(\$this->load->view\(\$this->config->get\(\'config_template\'\) \. \'\\/template\\/product\\/search.tpl\', \$data\)\);'; 
		    } else {*/
			$pattern = '\$this->response->setOutput\(\$this->render\(\)\);';
		    /*}*/
		} else {
		    /*if ($version >= 2) { 
			$pattern = '\$magicContent\s+\=\s+\$this->load->view\(\$this->config->get\(\'config_template\'\)\s+\.\s+\'\/template\/product\/search\.tpl\',\s+\$data\);';
		    } else {*/
			$pattern = '\$magicContent\s+\=\s+\$this->render\(TRUE\);';
		    /*}*/
		}
		
		/*if ($version >= 2) { 
		    $replace = '$magicContent = magiczoomplus($magicContent,$this,\'search\', $results);' . "\n\t";
		} else {*/
		    $replace = '$magicContent = magiczoomplus($magicContent,$this,\'search\', $results);' . "\n\t";
		/*}*/
		
		$replace = $this->addComments($replace);
		
		/*if ($version >= 2) { 
		    $replace = '$magicContent = $this->load->view($this->config->get(\'config_template\') . \'/template/product/search.tpl\', $data);' . "\n\t" . $replace;          
		} else {*/
		    $replace = '$magicContent = $this->render(TRUE);' . "\n\t" . $replace;          
		/*}*/

		if (!$this->wearenotalone) {
		    $replace = $replace . '$this->response->setOutput($magicContent, $this->config->get(\'config_compression\'));'. "\n\t";
		}     
		
		
		/*
		
		
		if ($version < 2) { 
		    $pattern = '\$this->response->setOutput\(\$this->render\(\)\);'; 
		    $replace = '$this->response->setOutput(magiczoomplus($this->render(TRUE),$this,\'search\', (isset($results) ? $results : array() )), $this->config->get(\'config_compression\'));';
		} else {
		    $pattern = '\$this->response->setOutput\(\$this->load->view\(\$this->config->get\(\'config_template\'\)\s+\.\s+\'\/template\/product\/search\.tpl\', \$data\)\);';
		    $replace = '$this->response->setOutput(magiczoomplus($this->load->view($this->config->get(\'config_template\') . \'/template/product/search.tpl\', $data),$this,\'search\',(isset($results) ? $results : array() )));	';
		}*/
		
		$c = preg_replace('/'.$pattern.'/is', $replace, $c, 2); //only first two needles replace



		file_put_contents($this->dir.'/catalog/controller/product/search.php', $c);
		/*vqmod fix start*/
		if ($vqName = $this->vqmod_fix('/catalog/controller/product/search.php')) {
		    file_put_contents($this->dir.$vqName, $c);
		}
		/*vqmod fix end*/


		/****************************************************************************** modify catalog/controller/module/latest.php ******************************************************************************/
		$c = file_get_contents($this->dir . '/catalog/controller/'.$this->modulesPath.'/latest.php');

		$pattern = 'class ControllerModuleLatest extends Controller {';
		$replace = 'global $aFolder;'. "\n\t" . 
			  'if (!defined(\'HTTP_ADMIN\')) define(\'HTTP_ADMIN\',\'admin\');'. "\n\t" .
			  '$aFolder = preg_replace(\'/.*\/([^\/].*)\//is\',\'$1\',HTTP_ADMIN);'. "\n\t" .
			  'if (!isset($GLOBALS[\'magictoolbox\'][\'magiczoomplus\']) && !isset($GLOBALS[\'magiczoomplus_module_loaded\'])) {'. "\n\t" .
			  '    include (preg_match("/components\/com_(ayelshop|aceshop|mijoshop)\/opencart\//ims",DIR_APPLICATION,$matches)?\'components/com_\'.$matches[1].\'/opencart/\':\'\').$aFolder.\'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/module.php\';'. "\n\t" .
			  '};'. "\n\t";

		$replace =  $this->addComments($replace);
		
		$replace =  $replace . "\n\t" .$pattern;

		$c = str_replace($pattern, $replace, $c);

		/*if ($version < 2) { */
		    $pattern = '\$this->render\(\);';
		    $replace = 'global $aFolder; include($aFolder.\'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/boxes.inc\');';
		    $replace = $this->addComments($replace);
		    $replace = '$this->render();'."\n\t".$replace;
		/*} else {
		  if (!$this->wearenotalone) {
			$pattern = 'return (.*?);';
			
			$replace = 'global $aFolder; include($aFolder.\'/controller/module/magiczoomplus-opencart-module/boxes.inc\');  ';
			$replace = $this->addComments($replace);
			$replace = '$contents = $1;'."\n\t". $replace .'return $contents;'."\n\t";
		    } else {
			$pattern = 'return\s+\$contents;';
			
			$replace = "\n\t".'global $aFolder; include($aFolder.\'/controller/module/magiczoomplus-opencart-module/boxes.inc\');';
			$replace = $this->addComments($replace);
			$replace = $replace.'return $contents;'."\n\t";
		    }
		    
		    
		}*/

		$c = preg_replace('/'.$pattern.'/is', $replace, $c, 1); //only first needle replace

		file_put_contents($this->dir.'/catalog/controller/'.$this->modulesPath.'/latest.php', $c);
		/*vqmod fix start*/
		if ($vqName = $this->vqmod_fix('/catalog/controller/'.$this->modulesPath.'/latest.php')) {
		    file_put_contents($this->dir.$vqName, $c);
		}
		/*vqmod fix end*/

	      /****************************************************************************** modify catalog/controller/module/special.php ******************************************************************************/
		$c = file_get_contents($this->dir . '/catalog/controller/'.$this->modulesPath.'/special.php');

		$pattern = 'class ControllerModuleSpecial extends Controller {';
		$replace = 'global $aFolder;'. "\n\t" . 
			  'if (!defined(\'HTTP_ADMIN\')) define(\'HTTP_ADMIN\',\'admin\');'. "\n\t" .
			  '$aFolder = preg_replace(\'/.*\/([^\/].*)\//is\',\'$1\',HTTP_ADMIN);'. "\n\t" .
			  'if (!isset($GLOBALS[\'magictoolbox\'][\'magiczoomplus\']) && !isset($GLOBALS[\'magiczoomplus_module_loaded\'])) {'. "\n\t" .
			  '    include (preg_match("/components\/com_(ayelshop|aceshop|mijoshop)\/opencart\//ims",DIR_APPLICATION,$matches)?\'components/com_\'.$matches[1].\'/opencart/\':\'\').$aFolder.\'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/module.php\';'. "\n\t" .
			  '};'. "\n\t";

		$replace =  $this->addComments($replace);
		$replace =  $replace . "\n\t" .$pattern;

		$c = str_replace($pattern, $replace, $c);

		/*if ($version < 2) { */
		    $pattern = '\$this->render\(\);';
		    $replace = 'global $aFolder; include($aFolder.\'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/boxes.inc\');';
		    $replace = $this->addComments($replace);
		    $replace = '$this->render();'."\n\t".$replace;
		/*} else {
		
		 if (!$this->wearenotalone) {
			$pattern = 'return (.*?);';
			
			$replace = 'global $aFolder; include($aFolder.\'/controller/module/magiczoomplus-opencart-module/boxes.inc\');  ';
			$replace = $this->addComments($replace);
			$replace = '$contents = $1;'."\n\t". $replace .'return $contents;'."\n\t";
		    } else {
			$pattern = 'return\s+\$contents;';
			
			$replace = "\n\t".'global $aFolder; include($aFolder.\'/controller/module/magiczoomplus-opencart-module/boxes.inc\');';
			$replace = $this->addComments($replace);
			$replace = $replace.'return $contents;'."\n\t";
		    }
		    
		    
		}*/

		$c = preg_replace('/'.$pattern.'/is', $replace, $c, 1); //only first needle replace

		file_put_contents($this->dir.'/catalog/controller/'.$this->modulesPath.'/special.php', $c);
		/*vqmod fix start*/
		if ($vqName = $this->vqmod_fix('/catalog/controller/'.$this->modulesPath.'/special.php')) {
		    file_put_contents($this->dir.$vqName, $c);
		}
		/*vqmod fix end*/

		/****************************************************************************** modify catalog/controller/module/featured.php ******************************************************************************/
		$c = file_get_contents($this->dir . '/catalog/controller/'.$this->modulesPath.'/featured.php');

		$pattern = 'class ControllerModuleFeatured extends Controller {';
		$replace = 'global $aFolder;'. "\n\t" . 
			  'if (!defined(\'HTTP_ADMIN\')) define(\'HTTP_ADMIN\',\'admin\');'. "\n\t" .
			  '$aFolder = preg_replace(\'/.*\/([^\/].*)\//is\',\'$1\',HTTP_ADMIN);'. "\n\t" .
			  'if (!isset($GLOBALS[\'magictoolbox\'][\'magiczoomplus\']) && !isset($GLOBALS[\'magiczoomplus_module_loaded\'])) {'. "\n\t" .
			  '    include (preg_match("/components\/com_(ayelshop|aceshop|mijoshop)\/opencart\//ims",DIR_APPLICATION,$matches)?\'components/com_\'.$matches[1].\'/opencart/\':\'\').$aFolder.\'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/module.php\';'. "\n\t" .
			  '};'. "\n\t";

		$replace = $this->addComments($replace);
		$replace =  $replace . "\n\t" .$pattern;

		$c = str_replace($pattern, $replace, $c);
		
		$pattern = '\$this->render\(\);';
		
		/*if ($version < 2) { */
		    $pattern = '\$this->render\(\);';
		    $replace = 'global $aFolder; include($aFolder.\'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/boxes.inc\');';
		    $replace = $this->addComments($replace);
		    $replace = '$this->render();'."\n\t".$replace;
		    $c = preg_replace('/'.$pattern.'/is', $replace."\n\t".'$1', $c, 1); //only first needle replace
		/*} else {
		
		   if (!$this->wearenotalone) {
			$pattern = 'return (.*?);';
			
			$replace = 'global $aFolder; include($aFolder.\'/controller/module/magiczoomplus-opencart-module/boxes.inc\');  ';
			$replace = $this->addComments($replace);
			$replace = '$contents = $1;'."\n\t". $replace .'return $contents;'."\n\t";
		    } else {
			$pattern = 'return\s+\$contents;';
			
			$replace = "\n\t".'global $aFolder; include($aFolder.\'/controller/module/magiczoomplus-opencart-module/boxes.inc\');';
			$replace = $this->addComments($replace);
			$replace = $replace.'return $contents;'."\n\t";
		    }
		    $c = preg_replace('/'.$pattern.'/is', $replace, $c, 1); //only first needle replace
		}*/


		$pattern = '\$product_info = \$this\-\>model_catalog_product\-\>getProduct\(\$product_id\)\;';
		$replace = '$product_info = $this->model_catalog_product->getProduct($product_id) ; $product_infos[] = $product_info;';
		//$replace = $this->addComments($replace);

		if (!$this->wearenotalone) {
		    $c = preg_replace('/'.$pattern.'/is', $replace, $c);
		}

		file_put_contents($this->dir.'/catalog/controller/'.$this->modulesPath.'/featured.php', $c);
		/*vqmod fix start*/
		if ($vqName = $this->vqmod_fix('/catalog/controller/'.$this->modulesPath.'/featured.php')) {
		    file_put_contents($this->dir.$vqName, $c);
		}
		/*vqmod fix end*/

		/****************************************************************************** modify catalog/controller/module/bestseller.php ******************************************************************************/
		$c = file_get_contents($this->dir . '/catalog/controller/'.$this->modulesPath.'/bestseller.php');

		$pattern = 'class ControllerModuleBestSeller extends Controller {';
		$replace = 'global $aFolder;'. "\n\t" . 
			  'if (!defined(\'HTTP_ADMIN\')) define(\'HTTP_ADMIN\',\'admin\');'. "\n\t" .
			  '$aFolder = preg_replace(\'/.*\/([^\/].*)\//is\',\'$1\',HTTP_ADMIN);'. "\n\t" .'if (!isset($GLOBALS[\'magictoolbox\'][\'magiczoomplus\']) && !isset($GLOBALS[\'magiczoomplus_module_loaded\'])) {'. "\n\t" .
			  '    include (preg_match("/components\/com_(ayelshop|aceshop|mijoshop)\/opencart\//ims",DIR_APPLICATION,$matches)?\'components/com_\'.$matches[1].\'/opencart/\':\'\').$aFolder.\'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/module.php\';'. "\n\t" .
			  '};'. "\n\t";

		$replace = $this->addComments($replace);
		$replace =  $replace . "\n\t" .$pattern;

		$c = str_replace($pattern, $replace, $c);

		/*if ($version < 2) { */
		    $pattern = '\$this->render\(\);';
		    $replace = 'global $aFolder; include($aFolder.\'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/boxes.inc\');';
		    $replace = $this->addComments($replace);
		    $replace = '$this->render();'."\n\t".$replace;
		    $c = preg_replace('/'.$pattern.'/is', $replace."\n\t".'$1', $c, 1); //only first needle replace
		/*} else {
		
		  if (!$this->wearenotalone) {
			$pattern = 'return (.*?);';
			
			$replace = 'global $aFolder; include($aFolder.\'/controller/module/magiczoomplus-opencart-module/boxes.inc\');  ';
			$replace = $this->addComments($replace);
			$replace = '$contents = $1;'."\n\t". $replace .'return $contents;'."\n\t";
		    } else {
			$pattern = 'return\s+\$contents;';
			
			$replace = "\n\t".'global $aFolder; include($aFolder.\'/controller/module/magiczoomplus-opencart-module/boxes.inc\');';
			$replace = $this->addComments($replace);
			$replace = $replace.'return $contents;'."\n\t";
		    }
		    $c = preg_replace('/'.$pattern.'/is', $replace, $c, 1); //only first needle replace
		    
		}*/

//		$c = preg_replace('/'.$pattern.'/is', $replace."\n\t".'$1', $c, 1); //only first needle replace

		file_put_contents($this->dir.'/catalog/controller/'.$this->modulesPath.'/bestseller.php', $c);
		/*vqmod fix start*/
		if ($vqName = $this->vqmod_fix('/catalog/controller/'.$this->modulesPath.'/bestseller.php')) {
		    file_put_contents($this->dir.$vqName, $c);
		}
		/*vqmod fix end*/

		/****************************************************************************** modify catalog/controller/common/home.php ******************************************************************************/
		/*if ($version < 2) { */
		    $c = file_get_contents($this->dir . '/catalog/controller/common/home.php');

		    if (!$this->wearenotalone) {
			$pattern = '\$this->response->setOutput\(\$this->render\(TRUE\), \$this->config->get\(\'config_compression\'\)\);';
		    } else {
			$pattern = '\$this->render\(\);';
		    }
		    
		    $replace = 'if(version_compare(VERSION, \'1.4.9\', \'<\')) {' . "\n\t\t\t" .
				  '$this->output = magiczoomplus($this->output,$this,\'latest_home_category\',$this->model_catalog_product->getLatestProducts(8));' . "\n\t\t" .
			      '}' . "\n\t\t";
		    $replace = $this->addComments($replace);
		    $replace = '$this->render();' . "\n\t\t" . $replace;

		    if (!$this->wearenotalone) {
			  $replace = $replace.'$this->response->setOutput($this->output, $this->config->get(\'config_compression\'));';
		    }

		    $c = preg_replace('/'.$pattern.'/is', $replace, $c, 1); //only first needle replace


		    file_put_contents($this->dir.'/catalog/controller/common/home.php', $c);
		    /*vqmod fix start*/
		    if ($vqName = $this->vqmod_fix('/catalog/controller/common/home.php')) {
			file_put_contents($this->dir.$vqName, $c);
		    }
		    /*vqmod fix end*/
		/*}*/
		/****************************************************************************** modify catalog/controller/common/header.php ******************************************************************************/
		$c = file_get_contents($this->dir . '/catalog/controller/common/header.php');

		$pattern = 'class ControllerCommonHeader extends Controller {';
		$replace = 'global $aFolder;'. "\n\t" . 
			  'if (!defined(\'HTTP_ADMIN\')) define(\'HTTP_ADMIN\',\'admin\');'. "\n\t" .
			  '$aFolder = preg_replace(\'/.*\/([^\/].*)\//is\',\'$1\',HTTP_ADMIN);'. "\n\t" .
			  'if (!isset($GLOBALS[\'magictoolbox\'][\'magiczoomplus\']) && !isset($GLOBALS[\'magiczoomplus_module_loaded\'])) {' . "\n\t\t" .
			      'include (preg_match("/components\/com_(ayelshop|aceshop|mijoshop)\/opencart\//ims",DIR_APPLICATION,$matches)?\'components/com_\'.$matches[1].\'/opencart/\':\'\').$aFolder.\'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/module.php\';'.
			  '}';

		$replace = $this->addComments($replace);
		$replace =  $replace . "\n\t" .$pattern;

		$c = str_replace($pattern, $replace, $c);

		/*if ($version < 2) {*/
		    $pattern = '\$this->render\(\);';
		    
		    $replace =  '$magiczoomplus_config = $this->config->get(\'magiczoomplus_settings\');'. "\n\t\t\t" .
				'if($magiczoomplus_config[\'magiczoomplus_status\'] != 0) { '. "\n\t\t\t" .
				    '$tool  = magiczoomplus_load_core_class($this);' . "\n\t\t\t" .
				    'if( magiczoomplus_use_effect_on($tool)) {' . "\n\t\t\t\t" .
					'$magicArray[\'magiczoomplus\'] = true;' . "\n\t\t\t" .
				    '}' . "\n\t\t" .
			    '}';
		/*} else {
		    if (!$this->wearenotalone) {
			$pattern = 'return(.*?);';
			
			$replace =  '$magiczoomplus_config= $this->config->get(\'magiczoomplus_settings\');'. "\n\t\t\t" .
				'if($magiczoomplus_config[\'magiczoomplus_status\'] != 0) {' . "\n\t\t\t" .
				      '$tool  = magiczoomplus_load_core_class($this);' . "\n\t\t\t" .
				      'if( magiczoomplus_use_effect_on($tool)) {' . "\n\t\t\t\t" .
					  '$magicArray[\'magiczoomplus\'] = true;' . "\n\t\t\t" .
				      '}' . "\n\t\t" .
				  '}';
		    } else {
			$pattern = '(\$contents\s+=\s+\$this->load->view\(\$this->config->get\(\'config_template\'\)\s+\.\s+\'\/template\/common\/header\.tpl\'\,\s+\$data\);)';
		    
			$replace =  '$1 '. "\n\t\t\t" .' $magiczoomplus_config= $this->config->get(\'magiczoomplus_settings\');'. "\n\t\t\t" .
				'if($magiczoomplus_config[\'magiczoomplus_status\'] != 0) {' . "\n\t\t\t" .
				      '$tool  = magiczoomplus_load_core_class($this);' . "\n\t\t\t" .
				      'if( magiczoomplus_use_effect_on($tool)) {' . "\n\t\t\t\t" .
					  '$magicArray[\'magiczoomplus\'] = true;' . "\n\t\t\t" .
				      '}' . "\n\t\t" .
				  '}';
			
		    }
	    
		    
		    
		}*/
		
		$replace = $this->addComments($replace);

		/*if ($version < 2) {*/
		    $replace = '$this->render();' . "\n\t\t" .$replace ;
		/*} else {
		    $replace = '$contents = $1;' . "\n\t\t" .$replace ;
		}*/

		if (!$this->wearenotalone) {
		    $replace =  '$magicArray = array("magiczoom" => false,
				  "magiczoomplus" => false,
				  "magicthumb" => false,
				  "magictouch" => false,
				  "magicmagnify" => false,
				  "magicmagnifyplus" => false,
				  "magicscroll" => false,
				  "magicslideshow" => false,
				  "magic360" => false);' . "\n\t\t" . $replace;
		   /*if ($version < 2) { */
			$replace = $replace . 'foreach ($magicArray as $toolId => $run) { '. "\n\t\t\t" .'
						    if ($run) { '. "\n\t\t\t" .'
							$func = "$toolId".\'_set_headers\'; '. "\n\t\t\t" .'
							$this->output = $func($this->output); '. "\n\t\t\t" .'
						    }
						}';
		    /*} else {
			$replace = $replace . 'foreach ($magicArray as $toolId => $run) { '. "\n\t\t\t" .'
						    if ($run) { '. "\n\t\t\t" .'
							$func = "$toolId".\'_set_headers\'; '. "\n\t\t\t" .'
							$contents = $func($contents); '. "\n\t\t\t" .'
						    }
						}
						return $contents;';
		    }*/
		}


		$c = preg_replace('/'.$pattern.'/is', $replace, $c, 1);

		file_put_contents($this->dir.'/catalog/controller/common/header.php', $c);
		/*vqmod fix start*/
		if ($vqName = $this->vqmod_fix('/catalog/controller/common/header.php')) {
		    file_put_contents($this->dir.$vqName, $c);
		}
		/*vqmod fix end*/
		
		if ($version >= 2 && 1==2) { //removed !! TODO
		    /****************************************************************************** modify catalog/controller/common/column_left.php ******************************************************************************/
		    $c = file_get_contents($this->dir . '/catalog/controller/common/column_left.php');
		    
		    $pattern = '(if\s+\(isset\(\$part\[1\]\)\s+\&\&\s+isset\(\$setting\[\$part\[1\]\]\)\)\s+\{)';
		    $replace =   '$1' . "\n\t\t\t" . '$setting[$part[1]][\'position\'] = \'column_left\';';
		    $c = preg_replace('/'.$pattern.'/is', $replace, $c);
		    
		    $pattern = '(\$setting_info\s+=\s+\$this\-\>model_extension_module\-\>getModule\(\$part\[1\]\);)';
		    $replace =   '$1' . "\n\t\t\t" . '$setting_info[\'position\'] = \'column_left\';';
		    $c = preg_replace('/'.$pattern.'/is', $replace, $c);
		    		    
		    file_put_contents($this->dir.'/catalog/controller/common/column_left.php', $c);
		    /*vqmod fix start*/
		    if ($vqName = $this->vqmod_fix('/catalog/controller/common/column_left.php')) {
			file_put_contents($this->dir.$vqName, $c);
		    }
		    /*vqmod fix end*/
		    /****************************************************************************** modify catalog/controller/common/column_right.php ******************************************************************************/
		    $c = file_get_contents($this->dir . '/catalog/controller/common/column_right.php');
		    
		    $pattern = '(if\s+\(isset\(\$part\[1\]\)\s+\&\&\s+isset\(\$setting\[\$part\[1\]\]\)\)\s+\{)';
		    $replace =   '$1' . "\n\t\t\t" . '$setting[$part[1]][\'position\'] = \'column_right\';';
		    $c = preg_replace('/'.$pattern.'/is', $replace, $c);
		    
		    $pattern = '(\$setting_info\s+=\s+\$this\-\>model_extension_module\-\>getModule\(\$part\[1\]\);)';
		    $replace =   '$1' . "\n\t\t\t" . '$setting_info[\'position\'] = \'column_right\';';
		    $c = preg_replace('/'.$pattern.'/is', $replace, $c);
		    
		    file_put_contents($this->dir.'/catalog/controller/common/column_right.php', $c);
		    /*vqmod fix start*/
		    if ($vqName = $this->vqmod_fix('/catalog/controller/common/column_right.php')) {
			file_put_contents($this->dir.$vqName, $c);
		    }
		    /*vqmod fix end*/
		    /****************************************************************************** modify catalog/controller/common/content_top ******************************************************************************/
		    $c = file_get_contents($this->dir . '/catalog/controller/common/content_top.php');
		    
		    $pattern = '(if\s+\(isset\(\$part\[1\]\)\s+\&\&\s+isset\(\$setting\[\$part\[1\]\]\)\)\s+\{)';
		    $replace =   '$1' . "\n\t\t\t" . '$setting[$part[1]][\'position\'] = \'content_top\';';
		    $c = preg_replace('/'.$pattern.'/is', $replace, $c);
		    
		    $pattern = '(\$setting_info\s+=\s+\$this\-\>model_extension_module\-\>getModule\(\$part\[1\]\);)';
		    $replace =   '$1' . "\n\t\t\t" . '$setting_info[\'position\'] = \'content_top\';';
		    $c = preg_replace('/'.$pattern.'/is', $replace, $c);
		    
		    file_put_contents($this->dir.'/catalog/controller/common/content_top.php', $c);
		    /*vqmod fix start*/
		    if ($vqName = $this->vqmod_fix('/catalog/controller/common/content_top.php')) {
			file_put_contents($this->dir.$vqName, $c);
		    }
		    /*vqmod fix end*/
		    /****************************************************************************** modify catalog/controller/common/content_bottom.php ******************************************************************************/
		    $c = file_get_contents($this->dir . '/catalog/controller/common/content_bottom.php');
		    
		    $pattern = '(if\s+\(isset\(\$part\[1\]\)\s+\&\&\s+isset\(\$setting\[\$part\[1\]\]\)\)\s+\{)';
		    $replace =   '$1' . "\n\t\t\t" . '$setting[$part[1]][\'position\'] = \'content_bottom\';';
		    $c = preg_replace('/'.$pattern.'/is', $replace, $c);
		    
		    $pattern = '(\$setting_info\s+=\s+\$this\-\>model_extension_module\-\>getModule\(\$part\[1\]\);)';
		    $replace =   '$1' . "\n\t\t\t" . '$setting_info[\'position\'] = \'content_bottom\';';
		    $c = preg_replace('/'.$pattern.'/is', $replace, $c);
		    
		    file_put_contents($this->dir.'/catalog/controller/common/content_bottom.php', $c);
		    /*vqmod fix start*/
		    if ($vqName = $this->vqmod_fix('/catalog/controller/common/content_bottom.php')) {
			file_put_contents($this->dir.$vqName, $c);
		    }
		    /*vqmod fix end*/
		}
		
		
		
	    } // if ! VQMOD
            return true;
        }

        function restoreStep_installFiles() {
            global $admin_folder_name;

            $files_to_remove=array('/'.$admin_folder_name.'/controller/'.$this->modulesPath.'/magiczoomplus.php',
                                    '/'.$admin_folder_name.'/view/template/'.$this->modulesPath.'/magiczoomplus.tpl');
            
            if (!$this->vqmodCheck() && version_compare($this->version,'2','<')) {
            
		$backups = array(
		    '/catalog/controller/product/product.php',
		    '/catalog/controller/product/category.php',
		    '/catalog/controller/product/manufacturer.php',
		    '/catalog/controller/product/search.php',
		    '/catalog/controller/common/home.php',
		    '/catalog/controller/common/header.php',
		    '/catalog/controller/common/column_left.php', 
		    '/catalog/controller/common/column_right.php', 
		    '/catalog/controller/common/content_top.php', 
		    '/catalog/controller/common/content_bottom.php',
		    '/catalog/controller/'.$this->modulesPath.'/latest.php',
		    '/catalog/controller/'.$this->modulesPath.'/bestseller.php',
		    '/catalog/controller/'.$this->modulesPath.'/special.php',
		    '/catalog/controller/'.$this->modulesPath.'/featured.php'

		);
		/*vqmod fix start*/
		$backups = $this->vqmod_fix($backups);
		/*vqmod fix end*/

		if (empty($this->wearenotalone)) {
		    if (!$this->multiply_check($backups)) {
			$this->wearenotalone = true;
		    }
		}

		if ($this->wearenotalone === false) {
		    $this->restoreFromBackups($backups);
		} else {
		    $this->removeModuleFromFiles($backups);
		}
            
            } else {
		if (version_compare($this->version,'2','<')) {
		    $files_to_remove[] = '/vqmod/xml/magiczoomplus_vqmod.xml';
		    if('magiczoomplus' == 'magic360'){
                        $files_to_remove[] = '/system/magic360gallery_vqmod.xml';
                    }
		} else {

                    $magic_mods = $this->get_modifications();
                    if (is_array($magic_mods) && count($magic_mods) < 2) { //remove the main and video ocmod only if it's the last extension to remove
                        $files_to_remove[] = '/system/magictoolbox-main.ocmod.xml';
                        $files_to_remove[] = '/'.$admin_folder_name.'/controller/'.$this->modulesPath.'/magictoolbox-module.inc';
                        
                        if(in_array('magiczoomplus',array('magiczoom','magiczoomplus','magicthumb'))) {
                            $files_to_remove[] = '/system/magictoolbox-video.ocmod.xml';
                        }
                    }
                    
		    $files_to_remove[] = '/system/magiczoomplus.ocmod.xml';
		    if('magiczoomplus' == 'magic360'){
                        $files_to_remove[] = '/system/magic360gallery.ocmod.xml';
                    }
		}
	    }

            $directories = glob($this->dir . '/'.$admin_folder_name.'/language/*' , GLOB_ONLYDIR);
            foreach ($directories as $ldir) {
                if (file_exists($ldir.'/'.$this->modulesPath.'/magiczoomplus.php')) {
                    $files_to_remove[] =  str_replace($this->dir,'',$ldir.'/module/magiczoomplus.php');
                }
            }

            $this->copyJsCss('remove');
            $this->removeFiles($files_to_remove);
            $this->removeDir($this->dir . '/'.$admin_folder_name.'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module');
            $this->removeDir($this->dir . '/'.$admin_folder_name.'/view/image/magiczoomplus-opencart-module');
            return true;
        }

        function upgrade($files) {
            global $admin_folder_name;
            
            $path = $this->dir . '/'.$admin_folder_name.'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/';
            foreach($files as $name => $file) {
                if(file_exists($path . $name)) {
                    unlink($path . $name);
                }
                file_put_contents($path . $name, $file);
                chmod($path . $name, 0755);
            }
            return true;
        }

        function copyJsCss($action = false) {

            global $admin_folder_name;

            if (file_exists($this->dir . '/'.$admin_folder_name.'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/graphics')) {
		$this->createDirRecursive($this->dir.'/catalog/view/css/graphics');
		$this->copyDir ($this->dir . '/'.$admin_folder_name.'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/graphics',
				$this->dir.'/catalog/view/css/graphics');
	    }
            
            if (file_exists($this->dir . '/'.$admin_folder_name.'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/fonts')) {
		$this->createDirRecursive($this->dir.'/catalog/view/css/fonts');
		$this->copyDir ($this->dir . '/'.$admin_folder_name.'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/fonts',
				$this->dir.'/catalog/view/css/fonts');
            }
            
            if (file_exists($this->dir . '/'.$admin_folder_name.'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/themes')) {
		$this->createDirRecursive($this->dir.'/catalog/view/css/themes');
		$this->copyDir ($this->dir . '/'.$admin_folder_name.'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/themes',
				$this->dir.'/catalog/view/css/themes');
            }
            
            $dirHandler = opendir($this->dir . '/'.$admin_folder_name.'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module');
            while (false !== ($entry = readdir($dirHandler))) {
                if (preg_match('/\.(js|css|swf)/is',strtolower($entry),$copyMatches)) {
                    switch ($copyMatches[1]) {
                        case 'js': { 
                                      if ($action == 'install') {
                                          $this->copyFile($this->dir.'/'.$admin_folder_name.'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/'.$entry,
							  $this->dir.'/catalog/view/javascript/'.$entry);
                                      }
                                      if ($action == 'remove') {
                                          //$this->removeFiles($this->dir.'/catalog/view/javascript/'.$entry);
                                      }

                                    } break;
			case 'swf': { 
                                      if ($action == 'install') {
                                          $this->copyFile($this->dir.'/'.$admin_folder_name.'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/'.$entry,
							  $this->dir.'/catalog/view/javascript/'.$entry);
					  if (file_exists($this->dir . '/'.$admin_folder_name.'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/loader.gif')) {
					      $this->copyFile($this->dir.'/'.$admin_folder_name.'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/loader.gif',
							      $this->dir.'/catalog/view/javascript/loader.gif');
					  }
							  
                                      }
                                      if ($action == 'remove') {
                                          //$this->removeFiles($this->dir.'/catalog/view/javascript/'.$entry);
                                      }

                                    } break;

                        case 'css': {
				      if ($action == 'install') {
					  $this->copyFile($this->dir.'/'.$admin_folder_name.'/controller/'.$this->modulesPath.'/magiczoomplus-opencart-module/'.$entry,
							  $this->dir.'/catalog/view/css/'.$entry); 
				      }
				      if ($action == 'remove') {
					  //$this->removeFiles($this->dir.'/catalog/view/css/'.$entry);
				      }

                                    } break;
                    }
                }
            }
            closedir($dirHandler);
        }

        function addComments ($content) {
            $content = '/* Magictoolbox magiczoomplus module BEGIN */'."\n\t".$content ."\n\t".'/* Magictoolbox magiczoomplus module END */'. "\n\t";
            return $content;
        }

        function removeModuleFromFiles ($files) {
            $pattern = '\/\*\s+Magictoolbox\s+magiczoomplus\s+module\s+BEGIN\s+\*\/.*?\/\*\s+Magictoolbox\s+magiczoomplus\s+module\s+END\s+\*\/';
            if (is_array($files)) {
                foreach ($files as $fileToClean){
                    if (file_exists($this->dir.$fileToClean)) {
                        $contents = file_get_contents($this->dir.$fileToClean);
                        $contents = preg_replace('/'.$pattern.'/is','',$contents);
                        file_put_contents($this->dir.$fileToClean, $contents);
                        @chmod($this->dir.$fileToClean, 0755);
                    }
                }
            }
        }

        function multiply_check ($files = false) {
            
            if (file_exists($this->dir.'/catalog/controller/'.$this->modulesPath.'/featured.php')) {
                $test = file_get_contents($this->dir.'/catalog/controller/'.$this->modulesPath.'/featured.php');
                $test_matches = substr_count($test,'boxes.inc');
                if ($test_matches > 0) return false;
            } 
            return true;
        }
        
        function get_modifications ($magicOnly = true, $coreModsOnly = true) {

            $systemdir = scandir(DIR_SYSTEM);
            $ocmods = array();
            
            foreach ($systemdir as $fname) {
                if ($magicOnly) { //get only magictoolbox ocmods
                    if (preg_match('/(magic.*?)\.ocmod\.xml/is',$fname,$matches)) {
                    
                        if ($coreModsOnly) { //get only tools ocmods (eg. no magic360gallery etc..)
                            if ($matches[1] != 'magic360gallery' && $matches[1] != 'magictoolbox-main') {
                                $ocmods[] = $matches[1];
                            }
                        } else {
                            $ocmods[] = $matches[1];
                        }
  
                    }
                } else { //get all ocmods
                    if (preg_match('/(^.*?)\.ocmod\.xml/is',$fname,$matches)) $ocmods[] = $matches[1];
                }
            }
            
            return $ocmods;
        }

        function vqmod_fix($input) {

            if (is_array($input)) {
                if (file_exists($this->dir.'/vqmod')) {
                    $files_array_add = array();
                    foreach ($input as $origFile) {
                        $vqName = '/vqmod/vqcache/vq2-'.str_replace('/','_',substr($origFile,1));
                        if (file_exists($this->dir.$vqName)) $files_array_add[] = $vqName;
                    }
                }
                if (is_array($files_array_add) && count($files_array_add) > 0) {
                    return array_merge($input,$files_array_add);
                } else {
                    return $input;
                }
            } else {
                $vqName = '/vqmod/vqcache/vq2-'.str_replace('/','_',substr($input,1));
                if (file_exists($this->dir.$vqName)) {
                    return $vqName;
                } else {
                    return false;
                }
            }
        }

        function getPlatformVersion() {
            if(file_exists($this->dir.'/index.php')) {
                $contents  = file_get_contents($this->dir.'/index.php');
                $match = array();
                if(preg_match('/define\s*\(\s*\'VERSION\'\s*,\s*\'([^\']*)\'\s*\)/i', $contents, $match)) {
                    return $match[1];
                } else if(file_exists($this->dir.'/system/startup.php')) {
                    //NOTE: for older OpenCart
                    $contents  = file_get_contents($this->dir.'/system/startup.php');
                    if(preg_match('/define\s*\(\s*\'VERSION\'\s*,\s*\'([^\']*)\'\s*\)/i', $contents, $match)) {
                        return $match[1];
                    }
                }
            }
            return '';
        }
        
        function getModulePath(){
            if (version_compare($this->getPlatformVersion(),'2.3','>=')) {
                $this->modulesPath = 'extension/module';
                $this->englishPath = 'en-gb';
            } else {
                $this->modulesPath = 'module';
                $this->englishPath = 'english';
            }
        
        }

    }

?>
