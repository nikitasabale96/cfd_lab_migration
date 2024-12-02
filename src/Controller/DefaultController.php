<?php /**
 * @file
 * Contains \Drupal\lab_migration\Controller\DefaultController.
 */

namespace Drupal\lab_migration\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Default controller for the lab_migration module.
 */
class DefaultController extends ControllerBase {

  public function lab_migration_proposal_pending() {
    /* get pending proposals to be approved */
    $pending_rows = [];
    //$pending_q = \Drupal::database()->query("SELECT * FROM {lab_migration_proposal} WHERE approval_status = 0 ORDER BY id DESC");
    $query = \Drupal::database()->select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('approval_status', 0);
    $query->orderBy('id', 'DESC');
    $pending_q = $query->execute();
    while ($pending_data = $pending_q->fetchObject()) {
      $pending_rows[$pending_data->id] = [
        date('d-m-Y', $pending_data->creation_date),
        l($pending_data->name, 'user/' . $pending_data->uid),
        $pending_data->lab_title,
        $pending_data->department,
        l('Approve', 'lab-migration/manage-proposal/approve/' . $pending_data->id) . ' | ' . l('Edit', 'lab-migration/manage-proposal/edit/' . $pending_data->id),
      ];
    }
    /* check if there are any pending proposals */
    if (!$pending_rows) {
      \Drupal::messenger()->addmessage(t('There are no pending proposals.'), 'status');
      return '';
    }
    $pending_header = [
      'Date of Submission',
      'Name',
      'Title of the Lab',
      'Department',
      'Action',
    ];
    //$output = theme_table($pending_header, $pending_rows);
    $output = theme('table', [
      'header' => $pending_header,
      'rows' => $pending_rows,
    ]);
    return $output;
  }

  public function lab_migration_solution_proposal_pending() {
    /* get list of solution proposal where the solution_provider_uid is set to some userid except 0 and solution_status is also 1 */
    $pending_rows = [];
    //$pending_q = \Drupal::database()->query("SELECT * FROM {lab_migration_proposal} WHERE solution_provider_uid != 0 AND solution_status = 1 ORDER BY id DESC");
    $query = \Drupal::database()->select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('solution_provider_uid', 0, '!=');
    $query->condition('solution_status', 1);
    $query->orderBy('id', 'DESC');
    $pending_q = $query->execute();
    while ($pending_data = $pending_q->fetchObject()) {
      $pending_rows[$pending_data->id] = [
        l($pending_data->name, 'user/' . $pending_data->uid),
        $pending_data->lab_title,
        l('Approve', 'lab-migration/manage-proposal/solution-proposal-approve/' . $pending_data->id),
      ];
    }
    /* check if there are any pending proposals */
    if (!$pending_rows) {
      \Drupal::messenger()->addmessage(t('There are no pending solution proposals.'), 'status');
      return '';
    }
    $pending_header = [
      'Proposer Name',
      'Title of the Lab',
      'Action',
    ];
    $output = theme('table', [
      'header' => $pending_header,
      'rows' => $pending_rows,
    ]);
    return $output;
  }

  public function lab_migration_proposal_pending_solution() {
    /* get pending proposals to be approved */
    $pending_rows = [];
    //$pending_q = \Drupal::database()->query("SELECT * FROM {lab_migration_proposal} WHERE approval_status = 1 ORDER BY id DESC");
    $query = \Drupal::database()->select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('approval_status', 1);
    $query->orderBy('id', 'DESC');
    $pending_q = $query->execute();
    while ($pending_data = $pending_q->fetchObject()) {
      $pending_rows[$pending_data->id] = [
        date('d-m-Y', $pending_data->creation_date),
        date('d-m-Y', $pending_data->approval_date),
        l($pending_data->name, 'user/' . $pending_data->uid),
        $pending_data->lab_title,
        $pending_data->department,
        l('Status', 'lab-migration/manage-proposal/status/' . $pending_data->id),
      ];
    }
    /* check if there are any pending proposals */
    if (!$pending_rows) {
      \Drupal::messenger()->addmessage(t('There are no proposals pending for solutions.'), 'status');
      return '';
    }
    $pending_header = [
      'Date of Submission',
      'Date of Approval',
      'Name',
      'Title of the Lab',
      'Department',
      'Action',
    ];
    $output = theme('table', [
      'header' => $pending_header,
      'rows' => $pending_rows,
    ]);
    return $output;
  }

  public function lab_migration_proposal_all() {
    /* get pending proposals to be approved */
    $proposal_rows = [];
    //$proposal_q = \Drupal::database()->query("SELECT * FROM {lab_migration_proposal} ORDER BY id DESC");
    $query = \Drupal::database()->select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->orderBy('id', 'DESC');
    $proposal_q = $query->execute();
    while ($proposal_data = $proposal_q->fetchObject()) {
      $approval_status = '';
      switch ($proposal_data->approval_status) {
        case 0:
          $approval_status = 'Pending';
          break;
        case 1:
          $approval_status = 'Approved';
          break;
        case 2:
          $approval_status = 'Dis-approved';
          break;
        case 3:
          $approval_status = 'Solved';
          break;
        default:
          $approval_status = 'Unknown';
          break;
      }
      $proposal_rows[] = [
        date('d-m-Y', $proposal_data->creation_date),
        l($proposal_data->name, 'user/' . $proposal_data->uid),
        $proposal_data->lab_title,
        $proposal_data->department,
        $approval_status,
        l('Status', 'lab-migration/manage-proposal/status/' . $proposal_data->id) . ' | ' . l('Edit', 'lab-migration/manage-proposal/edit/' . $proposal_data->id),
      ];
    }
    /* check if there are any pending proposals */
    if (!$proposal_rows) {
      \Drupal::messenger()->addmessage(t('There are no proposals.'), 'status');
      return '';
    }
    $proposal_header = [
      'Date of Submission',
      'Name',
      'Title of the Lab',
      'Department',
      'Status',
      'Action',
    ];
    $output = theme('table', [
      'header' => $proposal_header,
      'rows' => $proposal_rows,
    ]);
    return $output;
  }

  public function lab_migration_category_all() {
    /* get pending proposals to be approved */
    $proposal_rows = [];
    // $proposal_q = \Drupal::database()->query("SELECT * FROM {lab_migration_proposal} ORDER BY id DESC");
    $query = \Drupal::database()->select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->orderBy('id', 'DESC');
    $proposal_q = $query->execute();
    while ($proposal_data = $proposal_q->fetchObject()) {
      $proposal_rows[] = [
        date('d-m-Y', $proposal_data->creation_date),
        l($proposal_data->name, 'user/' . $proposal_data->uid),
        $proposal_data->lab_title,
        $proposal_data->department,
        $proposal_data->category,
        l('Edit Category', 'lab-migration/manage-proposal/category/edit/' . $proposal_data->id),
      ];
    }
    $proposal_header = [
      'Date of Submission',
      'Name',
      'Title of the Lab',
      'Department',
      'Category',
      'Action',
    ];
    $output = theme('table', [
      'header' => $proposal_header,
      'rows' => $proposal_rows,
    ]);
    return $output;
  }

  public function lab_migration_proposal_open() {
    $user = \Drupal::currentUser();
    /* get open proposal list */
    $proposal_rows = [];
    //$proposal_q = \Drupal::database()->query("SELECT * FROM {lab_migration_proposal} WHERE approval_status = 1 AND solution_provider_uid = 0");
    $query = \Drupal::database()->select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('approval_status', 1);
    $query->condition('solution_provider_uid', 0);
    $proposal_q = $query->execute();
    $proposal_q_count = $proposal_q->rowCount();
    if ($proposal_q_count != 0) {
      while ($proposal_data = $proposal_q->fetchObject()) {
        if ($proposal_data->problem_statement_file == '') {
          $problem_statement_file = "NA";
        }
        else {
          $problem_statement_file = l('View', 'lab-migration/download/problem-statement/' . $proposal_data->id);
        }
        $proposal_rows[] = [
          l($proposal_data->lab_title, 'lab-migration/show-proposal/' . $proposal_data->id),
          $problem_statement_file,
          l('Apply', 'lab-migration/show-proposal/' . $proposal_data->id),
        ];
      }
      $proposal_header = [
        'Title of the Lab',
        'Problem Statement',
        'Actions',
      ];
      $return_html = theme('table', [
        'header' => $proposal_header,
        'rows' => $proposal_rows,
      ]);
    }
    else {

      $return_html = 'There are no proposals are available to solve';
    }
    //$return_html = theme_table($proposal_header, $proposal_rows);
    return $return_html;
  }

  public function lab_migration_code_approval() {
    /* get a list of unapproved solutions */
    //$pending_solution_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution} WHERE approval_status = 0");
    $query = \Drupal::database()->select('lab_migration_solution');
    $query->fields('lab_migration_solution');
    $query->condition('approval_status', 0);
    $pending_solution_q = $query->execute();
    if (!$pending_solution_q) {
      \Drupal::messenger()->addmessage(t('There are no pending code approvals.'), 'status');
      return '';
    }
    $pending_solution_rows = [];
    while ($pending_solution_data = $pending_solution_q->fetchObject()) {
      /* get experiment data */
      //$experiment_q = \Drupal::database()->query("SELECT * FROM {lab_migration_experiment} WHERE id = %d", $pending_solution_data->experiment_id);
      $query = \Drupal::database()->select('lab_migration_experiment');
      $query->fields('lab_migration_experiment');
      $query->condition('id', $pending_solution_data->experiment_id);
      $experiment_q = $query->execute();
      $experiment_data = $experiment_q->fetchObject();
      /* get proposal data */
      // $proposal_q = \Drupal::database()->query("SELECT * FROM {lab_migration_proposal} WHERE id = %d", $experiment_data->proposal_id);
      $query = \Drupal::database()->select('lab_migration_proposal');
      $query->fields('lab_migration_proposal');
      $query->condition('id', $experiment_data->proposal_id);
      $proposal_q = $query->execute();
      $proposal_data = $proposal_q->fetchObject();
      /* get solution provider details */
      $solution_provider_user_name = '';
      $user_data = user_load($proposal_data->solution_provider_uid);
      if ($user_data) {
        $solution_provider_user_name = $user_data->name;
      }
      else {
        $solution_provider_user_name = '';
      }
      /* setting table row information */
      $pending_solution_rows[] = [
        $proposal_data->lab_title,
        $experiment_data->title,
        $proposal_data->name,
        $solution_provider_user_name,
        l('Edit', 'lab-migration/code-approval/approve/' . $pending_solution_data->id),
      ];
    }
    /* check if there are any pending solutions */
    if (!$pending_solution_rows) {
      \Drupal::messenger()->addmessage(t('There are no pending solutions'), 'status');
      return '';
    }
    $header = [
      'Title of the Lab',
      'Experiment',
      'Proposer',
      'Solution Provider',
      'Actions',
    ];
    //$output = theme_table($header, $pending_solution_rows);
    $output = theme('table', [
      'header' => $header,
      'rows' => $pending_solution_rows,
    ]);
    return $output;
  }

  public function lab_migration_list_experiments() {
    $user = \Drupal::currentUser();

    $proposal_data = lab_migration_get_proposal();
    if (!$proposal_data) {
      drupal_goto('');
      return;
    }

    $return_html = '<strong>Title of the Lab:</strong><br />' . $proposal_data->lab_title . '<br /><br />';
    $return_html .= '<strong>Proposer Name:</strong><br />' . $proposal_data->name_title . ' ' . $proposal_data->name . '<br /><br />';
    $return_html .= l('Upload Solution', 'lab-migration/code/upload') . '<br />';

    /* get experiment list */
    $experiment_rows = [];
    //$experiment_q = \Drupal::database()->query("SELECT * FROM {lab_migration_experiment} WHERE proposal_id = %d ORDER BY number ASC", $proposal_data->id);
    $query = \Drupal::database()->select('lab_migration_experiment');
    $query->fields('lab_migration_experiment');
    $query->condition('proposal_id', $proposal_data->id);
    $query->orderBy('number', 'ASC');
    $experiment_q = $query->execute();

    //var_dump($experiment_q->fetchObject());
    //die;

    while ($experiment_data = $experiment_q->fetchObject()) {


      $experiment_rows[] = [
        $experiment_data->number . ')&nbsp;&nbsp;&nbsp;&nbsp;' . $experiment_data->title,
        '',
        '',
        '',
      ];
      /* get solution list */
      //$solution_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution} WHERE experiment_id = %d ORDER BY id ASC", $experiment_data->id);
      $query = \Drupal::database()->select('lab_migration_solution');
      $query->fields('lab_migration_solution');
      $query->condition('experiment_id', $experiment_data->id);
      $query->orderBy('id', 'ASC');
      $solution_q = $query->execute();
      if ($solution_q) {
        while ($solution_data = $solution_q->fetchObject()) {
          $solution_status = '';
          switch ($solution_data->approval_status) {
            case 0:
              $solution_status = "Pending";
              break;
            case 1:
              $solution_status = "Approved";
              break;
            default:
              $solution_status = "Unknown";
              break;
          }
          if ($solution_data->approval_status == 0) {
            $experiment_rows[] = [
              "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $solution_data->code_number . "   " . $solution_data->caption,
              '',
              $solution_status,
              l('Delete', 'lab-migration/code/delete/' . $solution_data->id),
            ];
          }
          else {
            $experiment_rows[] = [
              "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $solution_data->code_number . "   " . $solution_data->caption,
              '',
              $solution_status,
              '',
            ];
          }
          /* get solution files */
          //$solution_files_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution_files} WHERE solution_id = %d ORDER BY id ASC", $solution_data->id);
          $query = \Drupal::database()->select('lab_migration_solution_files');
          $query->fields('lab_migration_solution_files');
          $query->condition('solution_id', $solution_data->id);
          $query->orderBy('id', 'ASC');
          $solution_files_q = $query->execute();

          if ($solution_files_q) {
            while ($solution_files_data = $solution_files_q->fetchObject()) {
              $code_file_type = '';
              switch ($solution_files_data->filetype) {
                case 'S':
                  $code_file_type = 'Source';
                  break;
                case 'R':
                  $code_file_type = 'Result';
                  break;
                case 'X':
                  $code_file_type = 'Xcox';
                  break;
                case 'U':
                  $code_file_type = 'Unknown';
                  break;
                default:
                  $code_file_type = 'Unknown';
                  break;
              }
              $experiment_rows[] = [
                "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . l($solution_files_data->filename, 'lab-migration/download/file/' . $solution_files_data->id),
                $code_file_type,
                '',
                '',
              ];
            }
          }
          /* get dependencies files */
          //$dependency_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution_dependency} WHERE solution_id = %d ORDER BY id ASC", $solution_data->id);
          $query = \Drupal::database()->select('lab_migration_solution_dependency');
          $query->fields('lab_migration_solution_dependency');
          $query->condition('solution_id', $solution_data->id);
          $query->orderBy('id', 'ASC');
          $dependency_q = $query->execute();
          while ($dependency_data = $dependency_q->fetchObject()) {
            //$dependency_files_q = \Drupal::database()->query("SELECT * FROM {lab_migration_dependency_files} WHERE id = %d", $dependency_data->dependency_id);
            $query = \Drupal::database()->select('lab_migration_dependency_files');
            $query->fields('lab_migration_dependency_files');
            $query->condition('id', $dependency_data->dependency_id);
            $dependency_files_q = $query->execute();
            $dependency_files_data = $dependency_files_q->fetchObject();
            $experiment_rows[] = [
              "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . l($dependency_files_data->filename, 'lab-migration/download/dependency/' . $dependency_files_data->id),
              'Dependency',
              '',
              '',
            ];
          }
        }
      }
    }

    $experiment_header = [
      'No. Title of the Experiment',
      'Type',
      'Status',
      'Actions',
    ];
    // $return_html .= theme_table($experiment_header, $experiment_rows);

    $return_html .= theme('table', [
      'header' => $experiment_header,
      'rows' => $experiment_rows,
    ]);
    return $return_html;
  }

  public function lab_migration_upload_code_delete() {
    $user = \Drupal::currentUser();

    $root_path = lab_migration_path();
    $solution_id = (int) arg(3);

    /* check solution */
    // $solution_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution} WHERE id = %d LIMIT 1", $solution_id);
    $query = \Drupal::database()->select('lab_migration_solution');
    $query->fields('lab_migration_solution');
    $query->condition('id', $solution_id);
    $query->range(0, 1);
    $solution_q = $query->execute();
    $solution_data = $solution_q->fetchObject();
    if (!$solution_data) {
      \Drupal::messenger()->addmessage('Invalid solution.', 'error');
      drupal_goto('lab-migration/code');
      return;
    }
    if ($solution_data->approval_status != 0) {
      \Drupal::messenger()->addmessage('You cannnot delete a solution after it has been approved. Please contact site administrator if you want to delete this solution.', 'error');
      drupal_goto('lab-migration/code');
      return;
    }

    //$experiment_q = \Drupal::database()->query("SELECT * FROM {lab_migration_experiment} WHERE id = %d LIMIT 1", $solution_data->experiment_id);
    $query = \Drupal::database()->select('lab_migration_experiment');
    $query->fields('lab_migration_experiment');
    $query->condition('id', $solution_data->experiment_id);
    $query->range(0, 1);
    $experiment_q = $query->execute();

    $experiment_data = $experiment_q->fetchObject();
    if (!$experiment_data) {
      \Drupal::messenger()->addmessage('You do not have permission to delete this solution.', 'error');
      drupal_goto('lab-migration/code');
      return;
    }

    //$proposal_q = \Drupal::database()->query("SELECT * FROM {lab_migration_proposal} WHERE id = %d AND solution_provider_uid = %d LIMIT 1", $experiment_data->proposal_id, $user->uid);
    $query = \Drupal::database()->select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('id', $experiment_data->proposal_id);
    $query->condition('solution_provider_uid', $user->uid);
    $query->range(0, 1);
    $proposal_q = $query->execute();
    $proposal_data = $proposal_q->fetchObject();
    if (!$proposal_data) {
      \Drupal::messenger()->addmessage('You do not have permission to delete this solution.', 'error');
      drupal_goto('lab-migration/code');
      return;
    }

    /* deleting solution files */
    if (lab_migration_delete_solution($solution_data->id)) {
      \Drupal::messenger()->addmessage('Solution deleted.', 'status');

      /* sending email */
      $email_to = $user->mail;

      $from = variable_get('lab_migration_from_email', '');
      $bcc = variable_get('lab_migration_emails', '');
      $cc = variable_get('lab_migration_cc_emails', '');

      $param['solution_deleted_user']['lab_title'] = $proposal_data->lab_title;
      $param['solution_deleted_user']['experiment_title'] = $experiment_data->title;
      $param['solution_deleted_user']['solution_number'] = $solution_data->code_number;
      $param['solution_deleted_user']['solution_caption'] = $solution_data->caption;
      $param['solution_deleted_user']['user_id'] = $user->uid;
      $param['solution_deleted_user']['headers'] = [
        'From' => $from,
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
        'Content-Transfer-Encoding' => '8Bit',
        'X-Mailer' => 'Drupal',
        'Cc' => $cc,
        'Bcc' => $bcc,
      ];

      if (!drupal_mail('lab_migration', 'solution_deleted_user', $email_to, language_default(), $param, $from, TRUE)) {
        \Drupal::messenger()->addmessage('Error sending email message.', 'error');
      }
    }
    else {
      \Drupal::messenger()->addmessage('Error deleting example.', 'status');
    }

    drupal_goto('lab-migration/code');
    return;
  }

  public function lab_migration_download_solution_file() {
    $solution_file_id = arg(3);
    $root_path = lab_migration_path();
    // $solution_files_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution_files} WHERE id = %d LIMIT 1", $solution_file_id);
    $solution_files_q = \Drupal::database()->query("SELECT lmsf.*, lmp.directory_name FROM lab_migration_solution_files lmsf JOIN lab_migration_solution lms JOIN lab_migration_experiment lme JOIN lab_migration_proposal lmp WHERE lms.id = lmsf.solution_id AND lme.id = lms.experiment_id AND lmp.id = lme.proposal_id AND lmsf.id = :solution_id LIMIT 1", [
      ':solution_id' => $solution_file_id
      ]);
    /*$query = \Drupal::database()->select('lab_migration_solution_files');
    $query->fields('lab_migration_solution_files');
    $query->condition('id', $solution_file_id);
    $query->range(0, 1);
    $solution_files_q = $query->execute();*/
    $solution_file_data = $solution_files_q->fetchObject();
    header('Content-Type: ' . $solution_file_data->filemime);
    //header('Content-Type: application/octet-stram');
    header('Content-disposition: attachment; filename="' . str_replace(' ', '_', ($solution_file_data->filename)) . '"');
    header('Content-Length: ' . filesize($root_path . $solution_file_data->directory_name . '/' . $solution_file_data->filepath));
    readfile($root_path . $solution_file_data->directory_name . '/' . $solution_file_data->filepath);
  }

  public function lab_migration_download_problem_statement() {
    $proposal_id = arg(3);
    $root_path = lab_migration_path();
    $proposal_q = \Drupal::database()->query("SELECT lmp.* FROM lab_migration_proposal lmp WHERE lmp.id = :proposal_id LIMIT 1", [
      ':proposal_id' => $proposal_id
      ]);
    $proposal_data = $proposal_q->fetchObject();
    header('Content-Type: doc');
    //header('Content-Type: application/octet-stram');
    header('Content-disposition: attachment; filename="' . str_replace(' ', '_', ($proposal_data->problem_statement_file)) . '"');
    header('Content-Length: ' . filesize($root_path . $proposal_data->directory_name . '/' . $proposal_data->problem_statement_file));
    readfile($root_path . $proposal_data->directory_name . '/' . $proposal_data->problem_statement_file);
  }

  public function lab_migration_download_solution() {
    $solution_id = arg(3);
    $root_path = lab_migration_path();
    /* get solution data */
    //$solution_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution} WHERE id = %d", $solution_id);
    $query = \Drupal::database()->select('lab_migration_solution');
    $query->fields('lab_migration_solution');
    $query->condition('id', $solution_id);
    $solution_q = $query->execute();
    $solution_data = $solution_q->fetchObject();
    //$experiment_q = \Drupal::database()->query("SELECT * FROM {lab_migration_experiment} WHERE id = %d", $solution_data->experiment_id);
    $query = \Drupal::database()->select('lab_migration_experiment');
    $query->fields('lab_migration_experiment');
    $query->condition('id', $solution_data->experiment_id);
    $experiment_q = $query->execute();
    $experiment_data = $experiment_q->fetchObject();
    //$solution_files_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution_files} WHERE solution_id = %d", $solution_id);
    /*$query = \Drupal::database()->select('lab_migration_solution_files');
    $query->fields('lab_migration_solution_files');
    $query->condition('solution_id', $solution_id);
    $solution_files_q = $query->execute();*/
    $solution_files_q = \Drupal::database()->query("SELECT lmsf.*, lmp.directory_name FROM lab_migration_solution_files lmsf JOIN lab_migration_solution lms JOIN lab_migration_experiment lme JOIN lab_migration_proposal lmp WHERE lms.id = lmsf.solution_id AND lme.id = lms.experiment_id AND lmp.id = lme.proposal_id AND lmsf.solution_id = :solution_id", [
      ':solution_id' => $solution_id
      ]);
    //$solution_dependency_files_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution_dependency} WHERE solution_id = %d", $solution_id);
    $query = \Drupal::database()->select('lab_migration_solution_dependency');
    $query->fields('lab_migration_solution_dependency');
    $query->condition('solution_id', $solution_id);
    $solution_dependency_files_q = $query->execute();
    $CODE_PATH = 'CODE' . $solution_data->code_number . '/';
    /* zip filename */
    $zip_filename = $root_path . 'zip-' . time() . '-' . rand(0, 999999) . '.zip';
    /* creating zip archive on the server */
    $zip = new ZipArchive();
    $zip->open($zip_filename, ZipArchive::CREATE);
    while ($solution_files_row = $solution_files_q->fetchObject()) {
      $zip->addFile($root_path . $solution_files_row->directory_name . '/' . $solution_files_row->filepath, $CODE_PATH . str_replace(' ', '_', ($solution_files_row->filename)));
    }
    /* dependency files */
    while ($solution_dependency_files_row = $solution_dependency_files_q->fetchObject()) {
      //$dependency_file_data = (\Drupal::database()->query("SELECT * FROM {lab_migration_dependency_files} WHERE id = %d LIMIT 1", $solution_dependency_files_row->dependency_id))->fetchObject();
      $query = \Drupal::database()->select('lab_migration_dependency_files');
      $query->fields('lab_migration_dependency_files');
      $query->condition('id', $solution_dependency_files_row->dependency_id);
      $query->range(0, 1);
      $dependency_file_data = $query->execute()->fetchObject();
      if ($dependency_file_data) {
        $zip->addFile($root_path . $dependency_file_data->filepath, $CODE_PATH . 'DEPENDENCIES/' . str_replace(' ', '_', ($dependency_file_data->filename)));
      }
    }
    $zip_file_count = $zip->numFiles;
    $zip->close();
    if ($zip_file_count > 0) {
      /* download zip file */
      header('Content-Type: application/zip');
      header('Content-disposition: attachment; filename="CODE' . $solution_data->code_number . '.zip"');
      header('Content-Length: ' . filesize($zip_filename));
      ob_clean();
      //flush();
      readfile($zip_filename);
      unlink($zip_filename);
    }
    else {
      \Drupal::messenger()->addmessage("There are no files in this solutions to download", 'error');
      drupal_goto('lab-migration/lab-migration-run');
    }
  }

  public function lab_migration_download_experiment() {
    $experiment_id = (int) arg(3);

    $root_path = lab_migration_path();
    /* get solution data */
    //$experiment_q = \Drupal::database()->query("SELECT * FROM {lab_migration_experiment} WHERE id = %d", $experiment_id);
    $query = \Drupal::database()->select('lab_migration_experiment');
    $query->fields('lab_migration_experiment');
    $query->condition('id', $experiment_id);
    $experiment_q = $query->execute();
    $experiment_data = $experiment_q->fetchObject();
    $EXP_PATH = 'EXP' . $experiment_data->number . '/';
    /* zip filename */
    $zip_filename = $root_path . 'zip-' . time() . '-' . rand(0, 999999) . '.zip';
    /* creating zip archive on the server */
    $zip = new ZipArchive();
    $zip->open($zip_filename, ZipArchive::CREATE);
    //$solution_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution} WHERE experiment_id = %d AND approval_status = 1", $experiment_id);
    $query = \Drupal::database()->select('lab_migration_solution');
    $query->fields('lab_migration_solution');
    $query->condition('experiment_id', $experiment_id);
    $query->condition('approval_status', 1);
    $solution_q = $query->execute();
    while ($solution_row = $solution_q->fetchObject()) {
      $CODE_PATH = 'CODE' . $solution_row->code_number . '/';
      // $solution_files_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution_files} WHERE solution_id = %d", $solution_row->id);
      $solution_files_q = \Drupal::database()->query("SELECT lmsf.*, lmp.directory_name FROM lab_migration_solution_files lmsf JOIN lab_migration_solution lms JOIN lab_migration_experiment lme JOIN lab_migration_proposal lmp WHERE lms.id = lmsf.solution_id AND lme.id = lms.experiment_id AND lmp.id = lme.proposal_id AND lmsf.solution_id = :solution_id", [
        ':solution_id' => $solution_row->id
        ]);
      /* $query = \Drupal::database()->select('lab_migration_solution_files');
        $query->fields('lab_migration_solution_files');
        $query->condition('solution_id', $solution_row->id);
        $solution_files_q = $query->execute();*/
      // $solution_dependency_files_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution_dependency} WHERE solution_id = %d", $solution_row->id);        
      while ($solution_files_row = $solution_files_q->fetchObject()) {
        $zip->addFile($root_path . $solution_files_row->directory_name . '/' . $solution_files_row->filepath, $EXP_PATH . $CODE_PATH . str_replace(' ', '_', ($solution_files_row->filename)));
      }
      /* dependency files */
      $query = \Drupal::database()->select('lab_migration_solution_dependency');
      $query->fields('lab_migration_solution_dependency');
      $query->condition('solution_id', $solution_row->id);
      $solution_dependency_files_q = $query->execute();
      while ($solution_dependency_files_row = $solution_dependency_files_q->fetchObject()) {
        //$dependency_file_data = (\Drupal::database()->query("SELECT * FROM {lab_migration_dependency_files} WHERE id = %d LIMIT 1", $solution_dependency_files_row->dependency_id))->fetchObject();
        $query = \Drupal::database()->select('lab_migration_dependency_files');
        $query->fields('lab_migration_dependency_files');
        $query->condition('id', $solution_dependency_files_row->dependency_id);
        $query->range(0, 1);
        $dependency_file_data = $query->execute()->fetchObject();
        if ($dependency_file_data) {
          $zip->addFile($root_path . $dependency_file_data->filepath, $EXP_PATH . $CODE_PATH . 'DEPENDENCIES/' . str_replace(' ', '_', ($dependency_file_data->filename)));
        }
      }
    }
    $zip_file_count = $zip->numFiles;
    $zip->close();
    if ($zip_file_count > 0) {
      /* download zip file */
      header('Content-Type: application/zip');
      header('Content-disposition: attachment; filename="EXP' . $experiment_data->number . '.zip"');
      header('Content-Length: ' . filesize($zip_filename));
      ob_clean();
      //flush();
      readfile($zip_filename);
      unlink($zip_filename);
    }
    else {
      \Drupal::messenger()->addmessage("There are no solutions in this experiment to download", 'error');
      drupal_goto('lab-migration/lab-migration-run');
    }
  }

  public function lab_migration_download_lab() {
    $user = \Drupal::currentUser();
    $lab_id = arg(3);
    //var_dump($lab_id);die;
    $root_path = lab_migration_path();
    /* get solution data */
    //$lab_q = \Drupal::database()->query("SELECT * FROM {lab_migration_proposal} WHERE id = %d", $lab_id);
    $query = \Drupal::database()->select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('id', $lab_id);
    $lab_q = $query->execute();
    $lab_data = $lab_q->fetchObject();
    $LAB_PATH = $lab_data->lab_title . '/';
    /* zip filename */
    $zip_filename = $root_path . 'zip-' . time() . '-' . rand(0, 999999) . '.zip';
    /* creating zip archive on the server */
    $zip = new ZipArchive();
    $zip->open($zip_filename, ZipArchive::CREATE);
    //$experiment_q = \Drupal::database()->query("SELECT * FROM {lab_migration_experiment} WHERE proposal_id = %d", $lab_id);
    $query = \Drupal::database()->select('lab_migration_experiment');
    $query->fields('lab_migration_experiment');
    $query->condition('proposal_id', $lab_id);
    $experiment_q = $query->execute();
    while ($experiment_row = $experiment_q->fetchObject()) {
      $EXP_PATH = 'EXP' . $experiment_row->number . '/';
      //$solution_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution} WHERE experiment_id = %d AND approval_status = 1", $experiment_row->id);
      $query = \Drupal::database()->select('lab_migration_solution');
      $query->fields('lab_migration_solution');
      $query->condition('experiment_id', $experiment_row->id);
      $query->condition('approval_status', 1);
      $solution_q = $query->execute();
      while ($solution_row = $solution_q->fetchObject()) {
        $CODE_PATH = 'CODE' . $solution_row->code_number . '/';
        //$solution_files_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution_files} WHERE solution_id = %d", $solution_row->id);

        $solution_files_q = \Drupal::database()->query("SELECT lmsf.*, lmp.directory_name FROM lab_migration_solution_files lmsf JOIN lab_migration_solution lms JOIN lab_migration_experiment lme JOIN lab_migration_proposal lmp WHERE lms.id = lmsf.solution_id AND lme.id = lms.experiment_id AND lmp.id = lme.proposal_id AND lmsf.solution_id = :solution_id", [
          ':solution_id' => $solution_row->id
          ]);
        /*$query = \Drupal::database()->select('lab_migration_solution_files');
            $query->fields('lab_migration_solution_files');
            $query->condition('solution_id', $solution_row->id);
            $solution_files_q = $query->execute();*/
        //$solution_dependency_files_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution_dependency} WHERE solution_id = %d", $solution_row->id);
        $query = \Drupal::database()->select('lab_migration_solution_dependency');
        $query->fields('lab_migration_solution_dependency');
        $query->condition('solution_id', $solution_row->id);
        $solution_dependency_files_q = $query->execute();
        while ($solution_files_row = $solution_files_q->fetchObject()) {
          $zip->addFile($root_path . $solution_files_row->directory_name . '/' . $solution_files_row->filepath, $EXP_PATH . $CODE_PATH . str_replace(' ', '_', ($solution_files_row->filename)));
          //var_dump($zip->numFiles);
        }
        // die;
            /* dependency files */
        while ($solution_dependency_files_row = $solution_dependency_files_q->fetchObject()) {
          //$dependency_file_data = (\Drupal::database()->query("SELECT * FROM {lab_migration_dependency_files} WHERE id = %d LIMIT 1", $solution_dependency_files_row->dependency_id))->fetchObject();
          $query = \Drupal::database()->select('lab_migration_dependency_files');
          $query->fields('lab_migration_dependency_files');
          $query->condition('id', $solution_dependency_files_row->dependency_id);
          $query->range(0, 1);
          $dependency_file_data = $query->execute()->fetchObject();
          if ($dependency_file_data) {
            $zip->addFile($root_path . $dependency_file_data->filepath, $EXP_PATH . $CODE_PATH . 'DEPENDENCIES/' . str_replace(' ', '_', ($dependency_file_data->filename)));
          }
        }
      }
    }
    $zip_file_count = $zip->numFiles;
    $zip->close();
    if ($zip_file_count > 0) {
      if ($user->uid) {
        /* download zip file */
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename="' . str_replace(' ', '_', $lab_data->lab_title) . '.zip"');
        header('Content-Length: ' . filesize($zip_filename));
        ob_clean();
        //flush();
        readfile($zip_filename);
        unlink($zip_filename);
      }
      else {
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename="' . str_replace(' ', '_', $lab_data->lab_title) . '.zip"');
        header('Content-Length: ' . filesize($zip_filename));
        header("Content-Transfer-Encoding: binary");
        header('Expires: 0');
        header('Pragma: no-cache');
        ob_end_flush();
        ob_clean();
        flush();
        readfile($zip_filename);
        unlink($zip_filename);
      }
    }
    else {
      \Drupal::messenger()->addmessage("There are no solutions in this Lab to download", 'error');
      drupal_goto('lab-migration/lab-migration-run');
    }
  }

  public function lab_migration_download_full_experiment() {
    $experiment_id = arg(3);
    $root_path = lab_migration_path();
    $APPROVE_PATH = 'APPROVED/';
    $PENDING_PATH = 'PENDING/';
    /* get solution data */
    //$experiment_q = \Drupal::database()->query("SELECT * FROM {lab_migration_experiment} WHERE id = %d", $experiment_id);
    $query = \Drupal::database()->select('lab_migration_experiment');
    $query->fields('lab_migration_experiment');
    $query->condition('id', $experiment_id);
    $experiment_q = $query->execute();
    $experiment_data = $experiment_q->fetchObject();
    $EXP_PATH = 'EXP' . $experiment_data->number . '/';
    /* zip filename */
    $zip_filename = $root_path . 'zip-' . time() . '-' . rand(0, 999999) . '.zip';
    /* creating zip archive on the server */
    $zip = new ZipArchive();
    $zip->open($zip_filename, ZipArchive::CREATE);
    /* approved solutions */
    //$solution_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution} WHERE experiment_id = %d AND approval_status = 1", $experiment_id);
    $query = \Drupal::database()->select('lab_migration_solution');
    $query->fields('lab_migration_solution');
    $query->condition('experiment_id', $experiment_id);
    $query->condition('approval_status', 1);
    $solution_q = $query->execute();
    while ($solution_row = $solution_q->fetchObject()) {
      $CODE_PATH = 'CODE' . $solution_row->code_number . '/';
      //$solution_files_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution_files} WHERE solution_id = %d", $solution_row->id);
        /*$query = \Drupal::database()->select('lab_migration_solution_files');
        $query->fields('lab_migration_solution_files');
        $query->condition('solution_id', $solution_row->id);
        $solution_files_q = $query->execute();*/
      $solution_files_q = \Drupal::database()->query("SELECT lmsf.*, lmp.directory_name FROM lab_migration_solution_files lmsf JOIN lab_migration_solution lms JOIN lab_migration_experiment lme JOIN lab_migration_proposal lmp WHERE lms.id = lmsf.solution_id AND lme.id = lms.experiment_id AND lmp.id = lme.proposal_id AND lmsf.id = :solution_id", [
        ':solution_id' => $solution_row->id
        ]);
      //$solution_dependency_files_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution_dependency} WHERE solution_id = %d", $solution_row->id);
      $query = \Drupal::database()->select('lab_migration_solution_dependency');
      $query->fields('lab_migration_solution_dependency');
      $query->condition('solution_id', $solution_row->id);
      $solution_dependency_files_q = $query->execute();
      while ($solution_files_row = $solution_files_q->fetchObject()) {
        $zip->addFile($root_path . $solution_files_row->directory_name . '/' . $solution_files_row->filepath, $APPROVE_PATH . $EXP_PATH . $CODE_PATH . $solution_files_row->filename);
      }
      /* dependency files */
      while ($solution_dependency_files_row = $solution_dependency_files_q->fetchObject()) {
        // $dependency_file_data = (\Drupal::database()->query("SELECT * FROM {lab_migration_dependency_files} WHERE id = %d LIMIT 1", $solution_dependency_files_row->dependency_id))->fetchObject();
        $query = \Drupal::database()->select('lab_migration_dependency_files');
        $query->fields('lab_migration_dependency_files');
        $query->condition('id', $solution_dependency_files_row->dependency_id);
        $query->range(0, 1);
        $dependency_file_data = $query->execute()->fetchObject();
        if ($dependency_file_data) {
          $zip->addFile($root_path . $dependency_file_data->filepath, $APPROVE_PATH . $EXP_PATH . $CODE_PATH . 'DEPENDENCIES/' . $dependency_file_data->filename);
        }
      }
    }
    /* unapproved solutions */
    // $solution_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution} WHERE experiment_id = %d AND approval_status = 0", $experiment_id);
    $query = \Drupal::database()->select('lab_migration_solution');
    $query->fields('lab_migration_solution');
    $query->condition('experiment_id', $experiment_id);
    $query->condition('approval_status', 0);
    $solution_q = $query->execute();
    while ($solution_row = $solution_q->fetchObject()) {
      $CODE_PATH = 'CODE' . $solution_row->code_number . '/';
      //$solution_files_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution_files} WHERE solution_id = %d", $solution_row->id);
        /*$query = \Drupal::database()->select('lab_migration_solution_files');
        $query->fields('lab_migration_solution_files');
        $query->condition('solution_id', $solution_row->id);
        $solution_files_q = $query->execute();*/
      $solution_files_q = \Drupal::database()->query("SELECT lmsf.*, lmp.directory_name FROM lab_migration_solution_files lmsf JOIN lab_migration_solution lms JOIN lab_migration_experiment lme JOIN lab_migration_proposal lmp WHERE lms.id = lmsf.solution_id AND lme.id = lms.experiment_id AND lmp.id = lme.proposal_id AND lmsf.id = :solution_id", [
        ':solution_id' => $solution_row->id
        ]);

      //$solution_dependency_files_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution_dependency} WHERE solution_id = %d", $solution_row->id);
      $query = \Drupal::database()->select('lab_migration_solution_dependency');
      $query->fields('lab_migration_solution_dependency');
      $query->condition('solution_id', $solution_row->id);
      $solution_dependency_files_q = $query->execute();
      while ($solution_files_row = $solution_files_q->fetchObject()) {
        $zip->addFile($root_path . $solution_files_row->directory_name . '/' . $solution_files_row->filepath, $PENDING_PATH . $EXP_PATH . $CODE_PATH . $solution_files_row->filename);
      }
      /* dependency files */
      while ($solution_dependency_files_row = $solution_dependency_files_q->fetchObject()) {
        // $dependency_file_data = (\Drupal::database()->query("SELECT * FROM {lab_migration_dependency_files} WHERE id = %d LIMIT 1", $solution_dependency_files_row->dependency_id))->fetchObject();
        $query = \Drupal::database()->select('lab_migration_dependency_files');
        $query->fields('lab_migration_dependency_files');
        $query->condition('id', $solution_dependency_files_row->dependency_id);
        $query->range(0, 1);
        $dependency_file_data = $query->execute()->fetchObject();
        if ($dependency_file_data) {
          $zip->addFile($root_path . $dependency_file_data->filepath, $PENDING_PATH . $EXP_PATH . $CODE_PATH . 'DEPENDENCIES/' . $dependency_file_data->filename);
        }
      }
    }
    $zip_file_count = $zip->numFiles;
    $zip->close();
    if ($zip_file_count > 0) {
      /* download zip file */
      header('Content-Type: application/zip');
      header('Content-disposition: attachment; filename="EXP' . $experiment_data->number . '.zip"');
      header('Content-Length: ' . filesize($zip_filename));
      readfile($zip_filename);
      unlink($zip_filename);
    }
    else {
      \Drupal::messenger()->addmessage("There are no solutions in this experiment to download", 'error');
      drupal_goto('lab-migration/code-approval/bulk');
    }
  }

  public function lab_migration_download_full_lab() {
    $lab_id = arg(3);
    var_dump($lab_id);
    //die;
    $root_path = lab_migration_path();
    $APPROVE_PATH = 'APPROVED/';
    $PENDING_PATH = 'PENDING/';
    /* get solution data */
    //$lab_q = \Drupal::database()->query("SELECT * FROM {lab_migration_proposal} WHERE id = %d", $lab_id);
    $query = \Drupal::database()->select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('id', $lab_id);
    $lab_q = $query->execute();
    $lab_data = $lab_q->fetchObject();
    $LAB_PATH = $lab_data->lab_title . '/';
    /* zip filename */
    $zip_filename = $root_path . 'zip-' . time() . '-' . rand(0, 999999) . '.zip';
    /* creating zip archive on the server */
    $zip = new ZipArchive();
    $zip->open($zip_filename, ZipArchive::CREATE);
    /* approved solutions */
    //$experiment_q = \Drupal::database()->query("SELECT * FROM {lab_migration_experiment} WHERE proposal_id = %d", $lab_id);
    $query = \Drupal::database()->select('lab_migration_experiment');
    $query->fields('lab_migration_experiment');
    $query->condition('proposal_id', $lab_id);
    $experiment_q = $query->execute();
    while ($experiment_row = $experiment_q->fetchObject()) {
      $EXP_PATH = 'EXP' . $experiment_row->number . '/';
      //$solution_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution} WHERE experiment_id = %d AND approval_status = 1", $experiment_row->id);
      $query = \Drupal::database()->select('lab_migration_solution');
      $query->fields('lab_migration_solution');
      $query->condition('experiment_id', $experiment_row->id);
      $query->condition('approval_status', 1);
      $solution_q = $query->execute();
      while ($solution_row = $solution_q->fetchObject()) {
        $CODE_PATH = 'CODE' . $solution_row->code_number . '/';
        //$solution_files_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution_files} WHERE solution_id = %d", $solution_row->id);
            /*$query = \Drupal::database()->select('lab_migration_solution_files');
            $query->fields('lab_migration_solution_files');
            $query->condition('solution_id', $solution_row->id);
            $solution_files_q = $query->execute();*/
        $solution_files_q = \Drupal::database()->query("SELECT lmsf.*, lmp.directory_name FROM lab_migration_solution_files lmsf JOIN lab_migration_solution lms JOIN lab_migration_experiment lme JOIN lab_migration_proposal lmp WHERE lms.id = lmsf.solution_id AND lme.id = lms.experiment_id AND lmp.id = lme.proposal_id AND lmsf.id = :solution_id", [
          ':solution_id' => $solution_row->id
          ]);
        //$solution_dependency_files_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution_dependency} WHERE solution_id = %d", $solution_row->id);
        $query = \Drupal::database()->select('lab_migration_solution_dependency');
        $query->fields('lab_migration_solution_dependency');
        $query->condition('solution_id', $solution_row->id);
        $solution_dependency_files_q = $query->execute();
        while ($solution_files_row = $solution_files_q->fetchObject()) {
          $zip->addFile($root_path . $solution_files_row->directory_name . '/' . $solution_files_row->filepath, $APPROVE_PATH . $EXP_PATH . $CODE_PATH . $solution_files_row->filename);
        }
        /* dependency files */
        while ($solution_dependency_files_row = $solution_dependency_files_q->fetchObject()) {
          //$dependency_file_data = (\Drupal::database()->query("SELECT * FROM {lab_migration_dependency_files} WHERE id = %d LIMIT 1", $solution_dependency_files_row->dependency_id))->fetchObject();
          $query = \Drupal::database()->select('lab_migration_dependency_files');
          $query->fields('lab_migration_dependency_files');
          $query->condition('id', $solution_dependency_files_row->dependency_id);
          $query->range(0, 1);
          $dependency_file_data = $query->execute()->fetchObject();
          if ($dependency_file_data) {
            $zip->addFile($root_path . $dependency_file_data->filepath, $APPROVE_PATH . $EXP_PATH . $CODE_PATH . 'DEPENDENCIES/' . $dependency_file_data->filename);
          }
        }
      }
      /* unapproved solutions */
      //$solution_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution} WHERE experiment_id = %d AND approval_status = 0", $experiment_row->id);
      $query = \Drupal::database()->select('lab_migration_solution');
      $query->fields('lab_migration_solution');
      $query->condition('experiment_id', $experiment_row->id);
      $query->condition('approval_status', 0);
      $solution_q = $query->execute();
      while ($solution_row = $solution_q->fetchObject()) {
        $CODE_PATH = 'CODE' . $solution_row->code_number . '/';
        //$solution_files_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution_files} WHERE solution_id = %d", $solution_row->id);
           /* $query = \Drupal::database()->select('lab_migration_solution_files');
            $query->fields('lab_migration_solution_files');
            $query->condition('solution_id', $solution_row->id);
            $solution_files_q = $query->execute();*/
        $solution_files_q = \Drupal::database()->query("SELECT lmsf.*, lmp.directory_name FROM lab_migration_solution_files lmsf JOIN lab_migration_solution lms JOIN lab_migration_experiment lme JOIN lab_migration_proposal lmp WHERE lms.id = lmsf.solution_id AND lme.id = lms.experiment_id AND lmp.id = lme.proposal_id AND lmsf.id = :solution_id", [
          ':solution_id' => $solution_row->id
          ]);

        //$solution_dependency_files_q = \Drupal::database()->query("SELECT * FROM {lab_migration_solution_dependency} WHERE solution_id = %d", $solution_row->id);
        $query = \Drupal::database()->select('lab_migration_solution_dependency');
        $query->fields('lab_migration_solution_dependency');
        $query->condition('solution_id', $solution_row->id);
        $solution_dependency_files_q = $query->execute();
        while ($solution_files_row = $solution_files_q->fetchObject()) {
          $zip->addFile($root_path . $solution_files_row->directory_name . '/' . $solution_files_row->filepath, $LAB_PATH . $PENDING_PATH . $EXP_PATH . $CODE_PATH . $solution_files_row->filename);
        }
        /* dependency files */
        while ($solution_dependency_files_row = $solution_dependency_files_q->fetchObject()) {
          //$dependency_file_data = (\Drupal::database()->query("SELECT * FROM {lab_migration_dependency_files} WHERE id = %d LIMIT 1", $solution_dependency_files_row->dependency_id))->fetchObject();
          $query = \Drupal::database()->select('lab_migration_dependency_files');
          $query->fields('lab_migration_dependency_files');
          $query->condition('id', $solution_dependency_files_row->dependency_id);
          $query->range(0, 1);
          $dependency_file_data = $query->execute()->fetchObject();
          if ($dependency_file_data) {
            $zip->addFile($root_path . $dependency_file_data->filepath, $LAB_PATH . $PENDING_PATH . $EXP_PATH . $CODE_PATH . 'DEPENDENCIES/' . $dependency_file_data->filename);
          }
        }
      }
    }
    $zip_file_count = $zip->numFiles;
    $zip->close();
    if ($zip_file_count > 0) {
      /* download zip file */
      ob_clean();
      //flush();
      header('Content-Type: application/zip');
      header('Content-disposition: attachment; filename="' . $lab_data->lab_title . '.zip"');
      header('Content-Length: ' . filesize($zip_filename));
      readfile($zip_filename);
      unlink($zip_filename);
    }
    else {
      \Drupal::messenger()->addmessage("There are no solutions in this lab to download", 'error');
      drupal_goto('lab-migration/code-approval/bulk');
    }
  }

  public function lab_migration_completed_labs_all() {
    $output = "";
    //$query = "SELECT * FROM {lab_migration_proposal} WHERE approval_status = 3";
    $query = \Drupal::database()->select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('approval_status', 3);
    $query->orderBy('approval_date', DESC);
    $result = $query->execute();
    //$result = \Drupal::database()->query($query);
    if ($result->rowCount() == 0) {
      $output .= "We will in process to update lab migration data";
    }
    else {
      $preference_rows = [];
      $i = $result->rowCount();
      while ($row = $result->fetchObject()) {
        $approval_date = date("Y", $row->approval_date);
        if ($row->problem_statement_file == '') {
          $problem_statement_file = "NA";
        }
        else {
          $problem_statement_file = l('View', 'lab-migration/download/problem-statement/' . $row->id);
        }
        $preference_rows[] = [
          $i,
          $row->university,
          l($row->lab_title, "lab-migration/experiments-list/" . $row->id),
          $problem_statement_file,
          $approval_date,
        ];
        $i--;
      }
      $preference_header = [
        'No',
        'Institute',
        'Lab',
        'Problem Statement',
        'Year',
      ];
      $output .= theme('table', [
        'header' => $preference_header,
        'rows' => $preference_rows,
      ]);
    }
    return $output;
  }

  public function lab_migration_labs_progress_all() {
    $page_content = "";
    //$query = "SELECT * FROM {lab_migration_proposal} WHERE approval_status = 1 and solution_status = 2";
    $query = \Drupal::database()->select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('approval_status', 1);
    $query->condition('solution_status', 2);
    $result = $query->execute();
    if ($result->rowCount() == 0) {
      $page_content .= "We will in process to update lab migration data";
    }
    else {
      //$result = \Drupal::database()->query($query);
      $page_content .= "<ol reversed>";
      while ($row = $result->fetchObject()) {
        $page_content .= "<li>";
        $page_content .= $row->university . " ({$row->lab_title})";
        $page_content .= "</li>";
      }
      $page_content .= "</ol>";
    }
    return $page_content;
  }

  public function lab_migration_download_lab_pdf() {
    $lab_id = arg(2);
    _latex_copy_script_file();
    $full_lab = arg(3);
    if ($full_lab == "1") {
      _latex_generate_files($lab_id, TRUE);
    }
    else {
      _latex_generate_files($lab_id, FALSE);
    }
  }

  public function lab_migration_delete_lab_pdf() {
    $lab_id = arg(3);
    lab_migration_del_lab_pdf($lab_id);
    \Drupal::messenger()->addmessage(t('Lab schedule for regeneration.'), 'status');
    drupal_goto('lab_migration/code_approval/bulk');
    return;
  }

  public function _list_all_lm_certificates() {
    $query = \Drupal::database()->query("SELECT * FROM lab_migration_certificate");
    $search_rows = [];
    $output = '';
    $details_list = $query->fetchAll();
    foreach ($details_list as $details) {
      if ($details->type == "Proposer") {
        $search_rows[] = [
          $details->lab_name,
          $details->institute_name,
          $details->name,
          $details->type,
          l('Download Certificate', 'lab-migration/certificate/generate-pdf/' . $details->proposal_id . '/' . $details->id),
          l('Edit Certificate', 'lab-migration/certificate/lm-proposer/form/edit/' . $details->proposal_id . '/' . $details->id),
        ];
      } //$details->type == "Proposer"
      else {
        $search_rows[] = [
          $details->lab_name,
          $details->institute_name,
          $details->name,
          $details->type,
          l('Download Certificate', 'lab-migration/certificate/generate-pdf/' . $details->proposal_id . '/' . $details->id),
          l('Edit Certificate', 'lab-migration/certificate/lm-participation/form/edit/' . $details->proposal_id . '/' . $details->id),
        ];
      }
    } //$details_list as $details
    $search_header = [
      'Lab Name',
      'Institute name',
      'Name',
      'Type',
      'Download Certificates',
      'Edit Certificates',
    ];
    $output .= theme('table', [
      'header' => $search_header,
      'rows' => $search_rows,
    ]);
    return $output;
  }

  public function verify_lab_migration_certificates($qr_code = 0) {
    $qr_code = arg(3);
    $page_content = "";
    if ($qr_code) {
      $page_content = verify_qrcode_lm_fromdb($qr_code);
    } //$qr_code
    else {
      $verify_certificates_form = \Drupal::formBuilder()->getForm("verify_lab_migration_certificates_form");
      $page_content = \Drupal::service("renderer")->render($verify_certificates_form);
    }
    return $page_content;
  }

}
