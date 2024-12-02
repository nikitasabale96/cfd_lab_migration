<?php


namespace Drupal\lab_migration\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Database\Database;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\RendererInterface;


class LabMigrationRunForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lab_migration_run_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $options_two = _ajax_get_experiment_list();
    $select_two = !$form_state->getValue(['lab_experiment_list']) ? $form_state->getValue(['lab_experiment_list']) : key($options_two);
    $url_lab_id = (int) arg(2);
    $url_experiment_id = (int) arg(3);
    $options_first = _list_of_labs($url_lab_id);
    if (!$url_lab_id) {
      $selected = !$form_state->getValue(['lab']) ? $form_state->getValue(['lab']) : key($options_first);
    }
    elseif ($url_lab_id == '') {
      $selected = 0;
    }
    else {
      $selected = $url_lab_id;
    }
    if (!$url_experiment_id) {
      $selected_experiment = !$form_state->getValue(['lab_experiment_list']) ? $form_state->getValue(['lab_experiment_list']) : key($options_two);
    }
    elseif ($url_experiment_id == '') {
      $selected_experiment = 0;
    }
    else {
      $selected_experiment = $url_experiment_id;
    }
    $form = [];
    $form['lab'] = [
      '#type' => 'select',
      '#title' => t('Title of the lab'),
      '#options' => _list_of_labs($selected),
      '#default_value' => $selected,
    ];
    /*if (!$url_lab_id)
      {
        $form['selected_lab'] = array(
            '#type' => 'item',
            '#markup' => '<div id="ajax_selected_lab"></div>'
        );
        $form['selected_lab_dwsim'] = array(
            '#type' => 'item',
            '#markup' => '<div id="ajax_selected_lab_dwsim"></div>'
        );
        $form['selected_lab_pdf'] = array(
            '#type' => 'item',
            '#markup' => '<div id="ajax_selected_lab_pdf"></div>'
        );
        $form['lab_details'] = array(
            '#type' => 'item',
            '#markup' => '<div id="ajax_lab_details"></div>'
        );
        $form['lab_experiment_list'] = array(
            '#type' => 'select',
            '#title' => t('Title of the experiment'),
            '#options' => _ajax_get_experiment_list($selected),
            '#default_value' => isset($form_state['values']['lab_experiment_list']) ? $form_state['values']['lab_experiment_list'] : '',
            '#ajax' => array(
                'callback' => 'ajax_solution_list_callback'
            ),
            '#prefix' => '<div id="ajax_selected_experiment">',
            '#suffix' => '</div>',
        );
        $form['download_experiment'] = array(
            '#type' => 'item',
            '#markup' => '<div id="ajax_download_experiments"></div>'
        );
        $form['lab_solution_list'] = array(
            '#type' => 'select',
            '#title' => t('Solution'),
            '#options' => _ajax_get_solution_list($select_two),
            //'#default_value' => isset($form_state['values']['lab_solution_list']) ? 
            //$form_state['values']['lab_solution_list'] : '',
            '#ajax' => array(
                'callback' => 'ajax_solution_files_callback'
            ),
            '#prefix' => '<div id="ajax_selected_solution">',
            '#suffix' => '</div>',
        );
        $form['download_solution'] = array(
            '#type' => 'item',
            '#markup' => '<div id="ajax_download_experiment_solution"></div>'
        );
        $form['edit_solution'] = array(
            '#type' => 'item',
            '#markup' => '<div id="ajax_edit_experiment_solution"></div>'
        );
        $form['solution_files'] = array(
            '#type' => 'item',
            //  '#title' => t('List of solution_files'),
            '#markup' => '<div id="ajax_solution_files"></div>',
            '#states' => array(
                'invisible' => array(
                    ':input[name="lab"]' => array(
                        'value' => 0
                    )
                )
            )
        );
      }
    else
      {*/
    $lab_default_value = $url_lab_id;
    $experiment_default_value = $url_experiment_id;
    /*$form['selected_lab'] = array(
            '#type' => 'item',
            '#markup' => '<div id="ajax_selected_lab">' . l('Download Lab Solutions', 'lab-migration/download/lab/' . $lab_default_value) . '</div>'
        );
        if ($lab_default_value == '2')
          {
            $form['selected_lab_dwsim'] = array(
                '#type' => 'item',
                '#markup' => '<div id="ajax_selected_lab_dwsim">' . l('Download Lab Solutions (dwsim Version)', 'lab-migration-uploads/dwsim_Version.zip') . '</div>'
            );
          }*/
    $form['selected_lab'] = [
      '#type' => 'item',
      '#markup' => '<div id="ajax_selected_lab">' . l('Download Lab Solutions', 'lab-migration/download/lab/' . $lab_default_value) . '</div>',
    ];
    $form['lab_experiment_list'] = [
      '#type' => 'select',
      '#title' => t('Title of the experiment'),
      '#options' => _ajax_get_experiment_list($selected),
      '#default_value' => $selected_experiment,
      '#ajax' => [
        'callback' => 'ajax_solution_list_callback'
        ],
      '#prefix' => '<div id="ajax_selected_experiment">',
      '#suffix' => '</div>',
      /*'#states' => array(
                'invisible' => array(
                    ':input[name="lab"]' => array(
                        'value' => 0
                    )
                )
            )*/
    ];
    $form['download_experiment'] = [
      '#type' => 'item',
      '#markup' => '<div id="ajax_download_experiments">' . l('Download Experiment', 'lab-migration/download/experiment/' . $selected_experiment) . '</div>',
    ];
    $form['lab_solution_list'] = [
      '#type' => 'select',
      '#title' => t('Solution'),
      '#options' => _ajax_get_solution_list($selected_experiment),
      //'#default_value' => isset($form_state['values']['lab_solution_list']) ? $form_state['values']['lab_solution_list'] : '',
            '#ajax' => [
        'callback' => 'ajax_solution_files_callback'
        ],
      '#prefix' => '<div id="ajax_selected_solution">',
      '#suffix' => '</div>',
      /*'#states' => array(
                'invisible' => array(
                    ':input[name="lab_experiment_list"]' => array(
                        'value' => 0
                    )
                )
            )*/
    ];
    $form['download_solution'] = [
      '#type' => 'item',
      '#markup' => '<div id="ajax_download_experiment_solution"></div>',
    ];
    $form['edit_solution'] = [
      '#type' => 'item',
      '#markup' => '<div id="ajax_edit_experiment_solution"></div>',
    ];
    $form['solution_files'] = [
      '#type' => 'item',
      //  '#title' => t('List of solution_files'),
            '#markup' => '<div id="ajax_solution_files"></div>',
      '#states' => [
        'invisible' => [
          ':input[name="lab_experiment_list"]' => [
            'value' => 0
            ]
          ]
        ],
    ];
    $form['lab_details'] = [
      '#type' => 'item',
      '#markup' => '<div id="ajax_lab_details">' . _lab_details($lab_default_value) . '</div>',
    ];
    $form['back_to_completed_labs'] = [
      '#type' => 'item',
      '#markup' => l('Back to Completed Labs', 'lab-migration/completed-labs'),
    ];
    //}
    return $form;
  }
  public function submitForm(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
  }
}
