<?php
    function facetly_template(){
        $facetly = facetly_api_init();
        if( !empty($_POST['facetly_template_hidden']) && $_POST['facetly_template_hidden'] == 'Y' ) {  
            $tplsearch = stripslashes($_POST['tplsearch']);  
            $tplfacet = stripslashes($_POST['tplfacet']);  
            try {
                $response = $facetly->templateUpdate($tplsearch,$tplfacet);
            } catch (Exception $e) {
                $error = $e->getMessage();
            }


            if ( !empty($error) ) {
                echo '<div class="error"><p><strong>'. $error. '</strong></p></div>';
            }
            if ( !empty($response) ) {
                echo '<div class="updated"><p><strong>'. $response. '</strong></p></div>';
                update_option('facetly_tplsearch', $tplsearch);
                update_option('facetly_tplfacet', $tplfacet);
            }
        } else {
            $tplsearch = get_option('facetly_tplsearch');
            $tplfacet = get_option('facetly_tplfacet');

            try {
                $template = $facetly->templateSelect();
                if (empty($tplfacet) && empty($tplsearch)) {
                    $tplsearch = $template->tplsearch;
                    $tplfacet = $template->tplfacet;
                }
            } catch (Exception $e) {
                echo '<div class="error"><p><strong>'. $e->getMessage(). '</strong></p></div>';
            }
            if (empty($template)) {
                echo '<div class="error"><p><strong>Can not connect to server, please check your consumer API configuration or contact our support if problem persist.</strong></p></div>';
            }
        } 
        
    ?> 

    <div class="wrap">  
        <?php    echo "<h2>" . __( 'Facetly Configuration' ) . "</h2>"; ?>  
        <?php echo "<h4>" . __( 'Facetly Template' ) . "</h4>"; ?>  
        <form name="facetly_settings" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">  
            <input type="hidden" name="facetly_template_hidden" value="Y">  
            <table>
                <tr>
                    <td><?php _e("Search Template:"); ?></td>
                    <td><textarea name="tplsearch" cols="150" rows="20"><?php echo $tplsearch ?></textarea></td>
                </tr>
                <tr>
                    <td><?php _e("Facet Template:"); ?></td>
                    <td><textarea name="tplfacet" cols="150" rows="20"><?php echo $tplfacet ?></textarea></td>
                </tr>
                <tr>
                    <td>
                        <p class="submit">  
                            <input type="submit" name="Submit" value="<?php _e('Submit Data' ) ?>" />  
                        </p>
                    </td>
                </tr>
            </table>
        </form>  
    </div> 
    <?php
}
