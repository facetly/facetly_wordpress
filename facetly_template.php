<?php
    function facetly_template(){
        $facetly = facetly_api_init();
        try {
            $template = $facetly->templateSelect();
            $fields = $facetly->fieldSelect();
        } catch (Exception $e) {
            echo '<div class="error"><p><strong>'. $e->getMessage(). '</strong></p></div>';
        }
        if( !empty($_POST['facetly_template_hidden']) && $_POST['facetly_template_hidden'] == 'Y' ) {  
            $tplsearch = stripslashes($_POST['tplsearch']);  
            $tplfacet = stripslashes($_POST['tplfacet']);  
            try {
                $response = $facetly->templateUpdate($tplsearch,$tplfacet);
                echo '<div class="updated"><p><strong>Template Saved</strong></p></div>';
                update_option('facetly_tplsearch', $tplsearch);
                update_option('facetly_tplfacet', $tplfacet);
            } catch (Exception $e) {
                $error = json_decode($e->getMessage());
                if ($error->status == 'error') {
                    $err_message = (is_string($error->message)) ? $error->message : '' ;
                    $tplsearch_err = $error->message->tplsearch;
                    $tplfacet_err = $error->message->tplfacet;

                    if (!empty($err_message)) {
                        echo '<div class="error"><p><strong>'. $err_message. '</strong></p></div>';
                    } else if (!empty($tplsearch_err) && !empty($tplfacet_err)) {
                        echo '<div class="error"><p><strong>'. $tplsearch_err. '<br/>'. $tplfacet_err. '</strong></p></div>';
                    } else if (!empty($tplsearch_err)) {
                        echo '<div class="error"><p><strong>'. $tplsearch_err. '</strong></p></div>';
                    } else if (!empty($tplfacet_err)) {
                        echo '<div class="error"><p><strong>'. $tplfacet_err. '</strong></p></div>';
                    } 
                }
            }
        } else {
            $tplsearch = get_option('facetly_tplsearch');
            $tplfacet = get_option('facetly_tplfacet');
            if (empty($tplfacet) && empty($tplsearch)) {
                $tplsearch = $template->tplsearch;
                $tplfacet = $template->tplfacet;
            }
            if (empty($template)) {
                echo '<div class="error"><p><strong>Can not connect to server, please check your consumer API configuration or contact our support if problem persist.</strong></p></div>';
            }
        }
        if (!empty($fields)) {
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
    }