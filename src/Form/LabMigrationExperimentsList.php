<?php

/**
 * @file
 * Contains \Drupal\lab_migration\Form\LabMigrationExperimentsList.
 */

namespace Drupal\lab_migration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

class LabMigrationExperimentsList extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lab_migration_experiments_list';
  }

  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $options_first = _list_of_labs();
    $options_two = _ajax_get_experiments_list();
    $select_two = !$form_state->getValue(['lab_experiment_list']) ? $form_state->getValue(['lab_experiment_list']) : key($options_two);
    $url_lab_id = (int) arg(2);
    if (!$url_lab_id) {
      $selected = !$form_state->getValue(['lab']) ? $form_state->getValue(['lab']) : key($options_first);
    }
    elseif ($url_lab_id == '') {
      $selected = 0;
    }
    else {
      $selected = $url_lab_id;
      ;
    }
    $form = [];
    $form['lab'] = [
      '#type' => 'select',
      '#title' => t('Title of the lab'),
      '#options' => _list_of_labs(),
      '#default_value' => $selected,
      '#ajax' => [
        'callback' => 'ajax_experiments_list_callback'
        ],
    ];
    if (!$url_lab_id) {
      $form['selected_lab'] = [
        '#type' => 'item',
        '#markup' => '<div id="ajax_selected_lab"></div>',
      ];
      $form['selected_lab_cfd'] = [
        '#type' => 'item',
        '#markup' => '<div id="ajax_selected_lab_cfd"></div>',
      ];
      $form['selected_lab_pdf'] = [
        '#type' => 'item',
        '#markup' => '<div id="ajax_selected_lab_pdf"></div>',
      ];
      /* $form['lab_details'] = array(
            '#type' => 'item',
            '#markup' => '<div id="ajax_lab_details"></div>'
        );*/
      $form['lab_experiment_list'] = [
        '#type' => 'item',
        '#title' => t('Title of the experiment'),
        '#markup' => _ajax_get_experiments_list($selected),
        //'#default_value' => isset($form_state['values']['lab_experiment_list']) ? $form_state['values']['lab_experiment_list'] : '',s
            '#prefix' => '<div id="ajax_selected_experiment">',
        '#suffix' => '</div>',
        '#states' => [
          'invisible' => [
            ':input[name="lab"]' => [
              'value' => 0
              ]
            ]
          ],
      ];
    }
    else {
      $lab_default_value = $url_lab_id;
      $form['selected_lab'] = [
        '#type' => 'item',
        '#markup' => '<div id="ajax_selected_lab">' . l('Download Lab Solutions', 'lab-migration/download/lab/' . $lab_default_value) . '</div>',
      ];
      /* $form['selected_lab_pdf'] = array(
        '#type' => 'item',
        '#markup' => '<div id="ajax_selected_lab_pdf">'. l('Download PDF of Lab Solutions', 'lab-migration/generate-lab/' . $lab_default_value . '/1') .'</div>',
        
        );*/
      if ($lab_default_value == '2') {
        $form['selected_lab_cfd'] = [
          '#type' => 'item',
          '#markup' => '<div id="ajax_selected_lab_cfd">' . l('Download Lab Solutions (OpenFOAM Version)', 'lab-migration-uploads/OpenFOAM_Version.zip') . '</div>',
        ];
      }
      /* $form['lab_details'] = array(
            '#type' => 'item',
            '#markup' => '<div id="ajax_lab_details">' . _lab_details($lab_default_value) . '</div>'
        );*/
      $form['lab_experiment_list'] = [
        '#type' => 'item',
        '#title' => t('Titile of the experiment'),
        //'#options' => _ajax_get_experiments_list($selected),
            '#markup' => _ajax_get_experiments_list($lab_default_value),
        // '#default_value' => isset($form_state['values']['lab_experiment_list']) ? //$form_state['values']['lab_experiment_list'] : '',
            '#prefix' => '<div id="ajax_selected_experiment">',
        '#suffix' => '</div>',
        '#states' => [
          'invisible' => [
            ':input[name="lab"]' => [
              'value' => 0
              ]
            ]
          ],
      ];
    }
    /*
    $form['message'] = array(
    '#type' => 'textarea',
    '#title' => t('If Dis-Approved please specify reason for Dis-Approval'),
    '#prefix' => '<div id= "message_submit">',   
    '#states' => array('invisible' => array(':input[name="lab"]' => array('value' => 0,),),), 
    
    );
    
    $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Submit'),      
    '#suffix' => '</div>',
    '#states' => array('invisible' => array(':input[name="lab"]' => array('value' => 0,),),),
    
    );*/
    return $form;
  }
  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
  }
}
?>
