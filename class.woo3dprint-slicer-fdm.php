<?php

class Woo3Dprint_Slicer_Fdm {
    // const API_HOST = 'rest.akismet.com';
    // const API_PORT = 80;
    // const MAX_DELAY_BEFORE_MODERATION_EMAIL = 86400; // One day in seconds
    
    private static $initiated = false;
    
    public $args = array();
    public $output = array();
    
    public static function init() {
        if ( ! self::$initiated ) {
            self::init_hooks();
        }
    }
    
    /**
    * Initializes WordPress hooks
    */
    private static function init_hooks() {
        self::$initiated = true;
        
        // Shortcode views
        // add_shortcode( 'woo3dprint', array( 'Woo3Dprint', 'shortcode_woo3dprint') );
        
        // Action for slice
        add_action( 'woo3dprint_cura', array( 'Woo3Dprint_Slicer_Fdm', 'slice' ), 10, 1 );
        // add_action( 'woo3dprint_slice_fdm', array( 'Woo3Dprint', 'auto_check_update_meta' ), 10, 2 );
        // add_filter( 'preprocess_comment', array( 'Woo3Dprint', 'auto_check_comment' ), 1 );
    }
    
    public static function slice($postId){
        //         update_field('email_do_usuario', $user['user_email'], $id_product);
        // update_field('nome_do_usuario', $user['user_name'], $id_product);
        // update_field('arquivo_orcado', $file['id'], $id_product);
        // // update_field('arquivo_orcado_gcode', $user_email, $id_product);
        // // update_field('resposta_do_gcode', $user_email, $id_product);
        $media = get_post_meta($postId, 'arquivo_orcado', true);

        $upload_wp_info = wp_upload_dir();

        $file_post = get_post($media);
        $location_file['id'] = $file_post->ID;
        $location_file['filename'] = array_slice(explode('/',$file_post->guid),-1,1)[0];
        $location_file['url'] = $file_post->guid;
        $location_file['path'] = str_replace($upload_wp_info['baseurl'], $upload_wp_info['basedir'], $file_post->guid);

        //System paramter declaration
        $dir_def = WOO3DPRINT__PLUGIN_DIR . "_inc/fdm-configs/definitions/";
        $dir_export = WOO3DPRINT__PLUGIN_DIR . "export/";

        //Default paramters
        $extraDefs = [];
        $settings = ['adhesion_type=None']; //You can also try: 'layer_height=0.2', 'infill_sparse_density=20', 'speed_print=50', 'adhesion_type=(Skirt/Brim/Raft/None)'
        $infile = $location_file['path'];
        $outfile = $dir_export . $location_file['id'] . "-" . $location_file['filename']  . ".gcode";
        $definitions = ["fdmprinter.def.json"];
        $defOverride = self::getPlatformPath();
        if ($defOverride == false){
            die("ERROR. Platform not supported.");    
        }
        // slice.php?slice={"infile":"test.stl","outfile":"test.gcode","extraDefs":["myprinter.def.json"]}

        if (file_exists($location_file['path'])){
            $sliceSettings = json_decode($_GET['slice'],true);
            if (isset($sliceSettings["infile"])){
                $infile = $sliceSettings["infile"];
            }
            if (isset($sliceSettings["outfile"])){
                $outfile = $sliceSettings["outfile"];
            }
            if (isset($sliceSettings["extraDefs"])){
                $extraDefs = $sliceSettings["extraDefs"];
            }
            if (isset($sliceSettings["extraSettings"])){
                $settings = $sliceSettings["extraSettings"];
            }
            
            //Check if there are extra definitions. If yes, load them as well.
            array_merge($definitions,$extraDefs);

            //Check if all the defination files exists.
            foreach ($definitions as $def_file){
                if (!file_exists($dir_def . $def_file)){
                    die("ERROR. Defination file not found: " . $dir_def . $def_file);
                }
            }
            
            //Check if the user defined an custom output file. If yes, use that as the default outfile name
            if (file_exists($outfile)){
                //File already exists. Delete the old one and replace with new one.
                unlink($outfile);
            }
            
            //Start parsing the command with the given paramters above
            $command = "slice -v -p ";
            
            //Prase defination files
            foreach ($definitions as $def){
                //Check if this defination is inside of the representative folder. 
                //If yes, use the one in platform folder. If not, use the one in definitions/
                if (file_exists($defOverride .  $def)){
                    $command = $command . '-j "' . $defOverride .  $def . '" ';
                }else{
                    $command = $command . '-j "' . $dir_def . $def . '" ';
                }
            }
            
            //Parse extra settings
            foreach ($settings as $setting){
                //Append settings into the command, without the need to wrap it in "" becase the setting value itself have it
                $command = $command . '-s ' . $setting . " ";
            }
            
            //Parse input output file
            $command = $command . '-o "' . $outfile . '" -l "' . $infile . '"';
            
            // var_dump($command);
            
            self::binarySelectExecution("CuraEngine",$command);
            
        }else{
            die("ERROR. slice info not given.");
        }
    }
    
    /**
    * Functions
    */

    public static function binarySelectExecution ($binaryName, $command){
        $layerHeight = 0.5;
        $infillPercentage = 100;
        $printSpeed = 30;
        $pricePerHour = 2;
        $pricePerGram = 0.2;
        $material = "PLA";
        $gCodeFile = 'D:\xampp\htdocs\producao\nanodesign.com.br\wp-content\plugins\woocommerce-3dprint/export/6153-Tampa-Telemachine.stl.gcode';
        $stlFile = 'D:\xampp\htdocs\producao\nanodesign.com.br/wp-content/uploads/2021/12/Tampa-Telemachine.stl';
        
        $args[] = "-v";
        $args[] = "-s layerThickness=".($layerHeight*1000);
        
        $args[] = "-s sparseInfillLineDistance=".(100*($layerHeight*1000)/$infillPercentage);
        $args[] = "-s printSpeed=".$printSpeed;
        $args[] = "-s infillSpeed=".$printSpeed;
        $args[] = "-s filamentDiameter=".(1.75*1000);
        $args[] = "-o ".$gCodeFile." ".$stlFile;
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            //Use windows binary
            $commandString = "start " . WOO3DPRINT__PLUGIN_FDM_SLICER_SRC_DIR .  "/windows/" . $binaryName . ".exe " . implode(" ", $args);
            // $commandString = "start " . WOO3DPRINT__PLUGIN_FDM_SLICER_SRC_DIR .  "/windows/" . $binaryName . ".exe " . $command;
            // print_r(pclose(popen($commandString, 'r')));
            // echo $command;
            exec($commandString, $output, $return);
        } else {
            //Use linux binary
            $cpuMode = exec("uname -m 2>&1",$output, $return_var);
            switch(trim($cpuMode)){
                case "armv7l": //raspberry pi 3B+
                case "armv6l": //Raspberry pi zero w
                        $commandString = "sudo " . WOO3DPRINT__PLUGIN_FDM_SLICER_SRC_DIR .  "\/armv6l/" . $binaryName . "_armv6l.elf " . $command; 
                    break;
               case "aarch64": //Armbian with ARMv8 / arm64
                        $commandString = "sudo " . WOO3DPRINT__PLUGIN_FDM_SLICER_SRC_DIR .  "\/arm64/" . $binaryName . "_arm64.elf " . $command;
                    break;
               case "i686": //x86 32bit CPU
               case "i386": //x86 32bit CPU
                    $commandString = "sudo " . WOO3DPRINT__PLUGIN_FDM_SLICER_SRC_DIR .  "\/i386/" . $binaryName . "_i386.elf " . $command;
                    break;
               case "x86_64": //x86-64 64bit CPU
                        $commandString = "sudo " . WOO3DPRINT__PLUGIN_FDM_SLICER_SRC_DIR .  "\/amd64/" . $binaryName . "_amd64.elf " . $command;
                    break;
               default:
                   //No idea why uname -m not working. In that case, x86 32bit binary is used.
                        $commandString = "sudo " . WOO3DPRINT__PLUGIN_FDM_SLICER_SRC_DIR .  "\/i386/" . $binaryName . "_i386.elf " . $command;
                    break;
            }
            pclose(popen($commandString . " > null.txt 2>&1 &", 'r'));
        }
        print_r(array("command"=>$commandString,"returnCode"=>$return,"commandOutput"=>$output));
    }

    public static function getPlatformPath(){
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return "windows/";
        } else {
            //Use linux binary
            $cpuMode = exec("uname -m 2>&1",$output, $return_var);
            switch(trim($cpuMode)){
                case "armv7l": //raspberry pi 3B+
                case "armv6l": //Raspberry pi zero w
                        return "armv6l/";
                    break;
               case "aarch64": //Armbian with ARMv8 / arm64
                        return "arm64/";
                    break;
               case "i686": //x86 32bit CPU
               case "i386": //x86 32bit CPU
                    return "i386/";
                    break;
               case "x86_64": //x86-64 64bit CPU
                        return "amd64/";
                    break;
               default:
                   //No idea why uname -m not working. In that case, x86 32bit binary is used.
                        return false;
                    break;
            }
        }
    }
    
    
    
    /**
    * 
    * Configuration for Slicer
    * 
    */
    
    public static $materials = array(
        "ABS"=>         array("fullName"=>"Acrylonitrile Butadiene Styrene", "price"=>array("amount"=>0.20, "unit"=>"USD/g"),  "canBeVaporPolished"=>true,  "density"=>array("amount"=>1.04, "unit"=>"g/cm^3"), "colors"=>array("#000000","#FFFFFF","#FFFAE0","#FF0F0F","#FF8324","#FFA8C8","#F7FF00","#70FF33","#140AA3","#8921FF","#9291B5","#87593E")),
        "PLA"=>         array("fullName"=>"Polylactic acid",                 "price"=>array("amount"=>0.25, "unit"=>"USD/g"),  "canBeVaporPolished"=>true,  "density"=>array("amount"=>1.25, "unit"=>"g/cm^3"), "colors"=>array("#000000","#FFFFFF","#FFFAE0","#FF0F0F","#FF8324","#FFA8C8","#F7FF00","#70FF33","#140AA3","#8921FF","#9291B5","#87593E")),
        "PC"=>          array("fullName"=>"Polycarbonate",                   "price"=>array("amount"=>0.60, "unit"=>"USD/g"),  "canBeVaporPolished"=>true,  "density"=>array("amount"=>1.20, "unit"=>"g/cm^3"), "colors"=>array("#000000","#FFFFFF","#FFFAE0","#FF0F0F","#FF8324","#FFA8C8","#F7FF00","#70FF33","#140AA3","#8921FF","#9291B5","#87593E")),
        "Nylon"=>       array("fullName"=>null,                              "price"=>array("amount"=>0.35, "unit"=>"USD/g"),  "canBeVaporPolished"=>false, "density"=>array("amount"=>1.25, "unit"=>"g/cm^3"), "colors"=>array("#000000","#FFFFFF","#FF0F0F","#70FF33","#140AA3","clear")),
        "LayWood"=>     array("fullName"=>null,                              "price"=>array("amount"=>0.80, "unit"=>"USD/g"),  "canBeVaporPolished"=>false, "density"=>array("amount"=>1.05, "unit"=>"g/cm^3"), "colors"=>array("#FFFFFF")),
        "BendLAY"=>     array("fullName"=>null,                              "price"=>array("amount"=>0.50, "unit"=>"USD/g"),  "canBeVaporPolished"=>true,  "density"=>array("amount"=>1.02, "unit"=>"g/cm^3"), "colors"=>array("#87593E")),
        "TPE"=>         array("fullName"=>"Thermoplastic elastomer",         "price"=>array("amount"=>0.60, "unit"=>"USD/g"),  "canBeVaporPolished"=>false, "density"=>array("amount"=>1.10, "unit"=>"g/cm^3"), "colors"=>array("clear")),
        "SoftPLA"=>     array("fullName"=>null,                              "price"=>array("amount"=>0.50, "unit"=>"USD/g"),  "canBeVaporPolished"=>false, "density"=>array("amount"=>1.15, "unit"=>"g/cm^3"), "colors"=>array("#000000","#FF0F0F","#140AA3","#FFFFFF")),
        "HIPS"=>        array("fullName"=>"High-impact Polystyrene",         "price"=>array("amount"=>0.20, "unit"=>"USD/g"),  "canBeVaporPolished"=>true,  "density"=>array("amount"=>1.06, "unit"=>"g/cm^3"), "colors"=>array("#FFFAE0"))
    );
    
    public static $printingCost = array("amount"=>"4.00","unit"=>"USD/hour");
    
    public static $addOns = array(
        "supportRemovalMultiplier"=>1.33,
        "vaporPolishingMultiplier"=>1.25,
        "rushPrintingMultiplier"=>1.50
    );
    
    public static $deliveryCosts = array(
        "base"=>array("amount"=>5.80,"unit"=>"USD"),
        "weightPrice"=>array("amount"=>0.01,"unit"=>"USD/g")
    );
    
    public static $slicerParams = array(
        "slicers"=>array("cura")
    );
    
    public static $layerHeights = array(
        "default"=>   array("amount"=>"0.254","unit"=>"mm"),
        "min"=>       array("amount"=>0.075,"unit"=>"mm"),
        "max"=>       array("amount"=>0.4, "unit"=>"mm")
    );
    
    public static $printSpeeds = array(
        "default"=> array("amount"=>50,"unit"=>"mm/s")
    );
}
