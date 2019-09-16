<?php
// $Id$
function lab_migration_proposal_pending()
  {
    /* get pending proposals to be approved */
    $pending_rows = array();
    //$pending_q = db_query("SELECT * FROM {lab_migration_proposal} WHERE approval_status = 0 ORDER BY id DESC");
    $query = db_select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('approval_status', 0);
    $query->orderBy('id', 'DESC');
    $pending_q = $query->execute();
    while ($pending_data = $pending_q->fetchObject())
      {
        $pending_rows[$pending_data->id] = array(
            date('d-m-Y', $pending_data->creation_date),
            l($pending_data->name, 'user/' . $pending_data->uid),
            $pending_data->lab_title,
            $pending_data->department,
            l('Approve', 'lab-migration/manage-proposal/approve/' . $pending_data->id) . ' | ' . l('Edit', 'lab-migration/manage-proposal/edit/' . $pending_data->id)
        );
      }
    /* check if there are any pending proposals */
    if (!$pending_rows)
      {
        drupal_set_message(t('There are no pending proposals.'), 'status');
        return '';
      }
    $pending_header = array(
        'Date of Submission',
        'Name',
        'Title of the Lab',
        'Department',
        'Action'
    );
    //$output = theme_table($pending_header, $pending_rows);
    $output = theme('table', array(
        'header' => $pending_header,
        'rows' => $pending_rows
    ));
    return $output;
  }
function lab_migration_proposal_pending_solution()
  {
    /* get pending proposals to be approved */
    $pending_rows = array();
    //$pending_q = db_query("SELECT * FROM {lab_migration_proposal} WHERE approval_status = 1 ORDER BY id DESC");
    $query = db_select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('approval_status', 1);
    $query->orderBy('id', 'DESC');
    $pending_q = $query->execute();
    while ($pending_data = $pending_q->fetchObject())
      {
        $pending_rows[$pending_data->id] = array(
            date('d-m-Y', $pending_data->creation_date),
            date('d-m-Y', $pending_data->approval_date),
            l($pending_data->name, 'user/' . $pending_data->uid),
            $pending_data->lab_title,
            $pending_data->department,
            l('Status', 'lab-migration/manage-proposal/status/' . $pending_data->id)
        );
      }
    /* check if there are any pending proposals */
    if (!$pending_rows)
      {
        drupal_set_message(t('There are no proposals pending for solutions.'), 'status');
        return '';
      }
    $pending_header = array(
        'Date of Submission',
        'Date of Approval',
        'Name',
        'Title of the Lab',
        'Department',
        'Action'
    );
    $output = theme('table', array(
        'header' => $pending_header,
        'rows' => $pending_rows
    ));
    return $output;
  }
function lab_migration_proposal_all()
  {
    /* get pending proposals to be approved */
    $proposal_rows = array();
    //$proposal_q = db_query("SELECT * FROM {lab_migration_proposal} ORDER BY id DESC");
    $query = db_select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->orderBy('id', 'DESC');
    $proposal_q = $query->execute();
    while ($proposal_data = $proposal_q->fetchObject())
      {
        $approval_status = '';
        switch ($proposal_data->approval_status)
        {
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
        $proposal_rows[] = array(
            date('d-m-Y', $proposal_data->creation_date),
            l($proposal_data->name, 'user/' . $proposal_data->uid),
            $proposal_data->lab_title,
            $proposal_data->department,
            $approval_status,
            l('Status', 'lab-migration/manage-proposal/status/' . $proposal_data->id) . ' | ' . l('Edit', 'lab-migration/manage-proposal/edit/' . $proposal_data->id)
        );
      }
    /* check if there are any pending proposals */
    if (!$proposal_rows)
      {
        drupal_set_message(t('There are no proposals.'), 'status');
        return '';
      }
    $proposal_header = array(
        'Date of Submission',
        'Name',
        'Title of the Lab',
        'Department',
        'Status',
        'Action'
    );
    $output = theme('table', array(
        'header' => $proposal_header,
        'rows' => $proposal_rows
    ));
    return $output;
  }
function lab_migration_category_all()
  {
    /* get pending proposals to be approved */
    $proposal_rows = array();
    // $proposal_q = db_query("SELECT * FROM {lab_migration_proposal} ORDER BY id DESC");
    $query = db_select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->orderBy('id', 'DESC');
    $proposal_q = $query->execute();
    while ($proposal_data = $proposal_q->fetchObject())
      {
        $proposal_rows[] = array(
            date('d-m-Y', $proposal_data->creation_date),
            l($proposal_data->name, 'user/' . $proposal_data->uid),
            $proposal_data->lab_title,
            $proposal_data->department,
            $proposal_data->category,
            l('Edit Category', 'lab-migration/manage-proposal/category/edit/' . $proposal_data->id)
        );
      }
    $proposal_header = array(
        'Date of Submission',
        'Name',
        'Title of the Lab',
        'Department',
        'Category',
        'Action'
    );
    $output = theme('table', array(
        'header' => $proposal_header,
        'rows' => $proposal_rows
    ));
    return $output;
  }
/******************************************************************************/
/************************** PROPOSAL APPROVAL FORM ****************************/
/******************************************************************************/
function lab_migration_proposal_approval_form($form, $form_state)
  {
    global $user;
    /* get current proposal */
    $proposal_id = (int) arg(3);
    //$proposal_q = db_query("SELECT * FROM {lab_migration_proposal} WHERE id = %d", $proposal_id);
    $query = db_select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q)
      {
        if ($proposal_data = $proposal_q->fetchObject())
          {
            /* everything ok */
          }
        else
          {
            drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
            drupal_goto('lab-migration/manage-proposal');
            return;
          }
      }
    else
      {
        drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
        drupal_goto('lab-migration/manage-proposal');
        return;
      }
    // var_dump($proposal_data->name_title);
    //    die;
    $form['name'] = array(
        '#type' => 'item',
        '#markup' => l($proposal_data->name_title . ' ' . $proposal_data->name, 'user/' . $proposal_data->uid),
        '#title' => t('Name')
    );
    $form['email_id'] = array(
        '#type' => 'item',
        '#markup' => user_load($proposal_data->uid)->mail,
        '#title' => t('Email')
    );
    $form['contact_ph'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->contact_ph,
        '#title' => t('Contact No.')
    );
    $form['department'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->department,
        '#title' => t('Department/Branch')
    );
    $form['university'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->university,
        '#title' => t('University/Institute')
    );
    $form['country'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->country,
        '#title' => t('Country')
    );
    $form['all_state'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->state,
        '#title' => t('State')
    );
    $form['city'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->city,
        '#title' => t('City')
    );
    $form['pincode'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->pincode,
        '#title' => t('Pincode/Postal code')
    );
    $form['lab_title'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->lab_title,
        '#title' => t('Title of the Lab')
    );
    /* get experiment details */
    $experiment_list = '<ul>';
    //$experiment_q = db_query("SELECT * FROM {lab_migration_experiment} WHERE proposal_id = %d ORDER BY id ASC", $proposal_id);
    $query = db_select('lab_migration_experiment');
    $query->fields('lab_migration_experiment');
    $query->condition('proposal_id', $proposal_id);
    $query->orderBy('id', 'ASC');
    $experiment_q = $query->execute();
    while ($experiment_data = $experiment_q->fetchObject())
      {
        $experiment_list .= '<li>' . $experiment_data->title . '</li>Description of Experiment : ' . $experiment_data->description . '<br>';
      }
    $experiment_list .= '</ul>';
    $form['experiment'] = array(
        '#type' => 'item',
        '#markup' => $experiment_list,
        '#title' => t('Experiments')
    );
    if ($proposal_data->solution_provider_uid == 0)
      {
        $solution_provider = "User will not provide solution, we will have to provide solution";
      }
    else if ($proposal_data->solution_provider_uid == $proposal_data->uid)
      {
        $solution_provider = "Proposer will provide the solution of the lab";
      }
    else
      {
        $solution_provider_user_data = user_load($proposal_data->solution_provider_uid);
        if ($solution_provider_user_data)
            $solution_provider = "Solution will be provided by user " . l($solution_provider_user_data->name, 'user/' . $proposal_data->solution_provider_uid);
        else
            $solution_provider = "User does not exists";
      }
    $form['solution_provider_uid'] = array(
        '#type' => 'item',
        '#title' => t('Do you want to provide the solution'),
        '#markup' => $solution_provider
    );
    /* $form['solution_display'] = array(
    '#type' => 'item',
    '#title' => t('Do you want to display the solution on the www.dwsim.fossee.in website'),
    '#markup' => ($proposal_data->solution_display == 1) ? "Yes" : "No",
    );*/
    $form['approval'] = array(
        '#type' => 'radios',
        '#title' => t('Lab migration proposal'),
        '#options' => array(
            '1' => 'Approve',
            '2' => 'Disapprove'
        ),
        '#required' => TRUE
    );
    $form['message'] = array(
        '#type' => 'textarea',
        '#title' => t('Reason for disapproval'),
        '#attributes' => array(
            'placeholder' => t('Enter reason for disapproval in minimum 30 characters '),
            'cols' => 50,
            'rows' => 4
        ),
        '#states' => array(
            'visible' => array(
                ':input[name="approval"]' => array(
                    'value' => '2'
                )
            )
        )
    );
    $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Submit')
    );
    $form['cancel'] = array(
        '#type' => 'item',
        '#markup' => l(t('Cancel'), 'lab-migration/manage-proposal')
    );
    return $form;
  }
function lab_migration_proposal_approval_form_validate($form, &$form_state)
  {
    if ($form_state['values']['approval'] == 2)
      {
        if ($form_state['values']['message'] == '')
          {
            form_set_error('message', t('Reason for disapproval could not be empty'));
          }
      }
  }
function lab_migration_proposal_approval_form_submit($form, &$form_state)
  {
    global $user;
    /* get current proposal */
    $proposal_id = (int) arg(3);
    // $proposal_q = db_query("SELECT * FROM {lab_migration_proposal} WHERE id = %d", $proposal_id);
    $query = db_select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q)
      {
        if ($proposal_data = $proposal_q->fetchObject())
          {
            /* everything ok */
          }
        else
          {
            drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
            drupal_goto('lab-migration/manage-proposal');
            return;
          }
      }
    else
      {
        drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
        drupal_goto('lab-migration/manage-proposal');
        return;
      }
    if ($form_state['values']['approval'] == 1)
      {
        $query = "UPDATE {lab_migration_proposal} SET approver_uid = :uid, approval_date = :date, approval_status = 1, solution_status = 2 WHERE id = :proposal_id";
        $args = array(
            ":uid" => $user->uid,
            ":date" => time(),
            ":proposal_id" => $proposal_id
        );
        db_query($query, $args);
        /* sending email */
        $user_data = user_load($proposal_data->uid);
        $email_to = $user_data->mail;
        $from = variable_get('lab_migration_from_email', '');
        $bcc = $user->mail . ', ' . variable_get('lab_migration_emails', '');
        $cc = variable_get('lab_migration_cc_emails', '');
        $param['proposal_approved']['proposal_id'] = $proposal_id;
        $param['proposal_approved']['user_id'] = $proposal_data->uid;
        $param['proposal_approved']['headers'] = array(
            'From' => $from,
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
            'Content-Transfer-Encoding' => '8Bit',
            'X-Mailer' => 'Drupal',
            'Cc' => $cc,
            'Bcc' => $bcc
        );
        if (!drupal_mail('lab_migration', 'proposal_approved', $email_to, language_default(), $param, $from, TRUE))
            drupal_set_message('Error sending email message.', 'error');
        drupal_set_message('Lab migration proposal No. ' . $proposal_id . ' approved. User has been notified of the approval.', 'status');
        drupal_goto('lab-migration/manage-proposal');
        return;
      }
    else if ($form_state['values']['approval'] == 2)
      {
        $query = "UPDATE {lab_migration_proposal} SET approver_uid = :uid, approval_date = :date, approval_status = 2, message = :message, solution_provider_uid = 0, solution_status = 0 WHERE id = :proposal_id";
        $args = array(
            ":uid" => $user->uid,
            ":date" => time(),
            ":message" => $form_state['values']['message'],
            ":proposal_id" => $proposal_id
        );
        $result = db_query($query, $args);
        /* sending email */
        $user_data = user_load($proposal_data->uid);
        $email_to = $user_data->mail;
        $from = variable_get('lab_migration_from_email', '');
        $bcc = $user->mail . ', ' . variable_get('lab_migration_emails', '');
        $cc = variable_get('lab_migration_cc_emails', '');
        $param['proposal_disapproved']['proposal_id'] = $proposal_id;
        $param['proposal_disapproved']['user_id'] = $proposal_data->uid;
        $param['proposal_disapproved']['headers'] = array(
            'From' => $from,
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
            'Content-Transfer-Encoding' => '8Bit',
            'X-Mailer' => 'Drupal',
            'Cc' => $cc,
            'Bcc' => $bcc
        );
        if (!drupal_mail('lab_migration', 'proposal_disapproved', $email_to, language_default(), $param, $from, TRUE))
            drupal_set_message('Error sending email message.', 'error');
        drupal_set_message('Lab migration proposal No. ' . $proposal_id . ' dis-approved. User has been notified of the dis-approval.', 'error');
        drupal_goto('lab-migration/manage-proposal');
        return;
      }
  }
/******************************************************************************/
/*************************** PROPOSAL STATUS FORM *****************************/
/******************************************************************************/
function lab_migration_proposal_status_form($form, $form_state)
  {
    global $user;
    /* get current proposal */
    $proposal_id = (int) arg(3);
    //$proposal_q = db_query("SELECT * FROM {lab_migration_proposal} WHERE id = %d", $proposal_id);
    $query = db_select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q)
      {
        if ($proposal_data = $proposal_q->fetchObject())
          {
            /* everything ok */
          }
        else
          {
            drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
            drupal_goto('lab-migration/manage-proposal');
            return;
          }
      }
    else
      {
        drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
        drupal_goto('lab-migration/manage-proposal');
        return;
      }
    $form['name'] = array(
        '#type' => 'item',
        '#markup' => l($proposal_data->name_title . ' ' . $proposal_data->name, 'user/' . $proposal_data->uid),
        '#title' => t('Name')
    );
    $form['email_id'] = array(
        '#type' => 'item',
        '#markup' => user_load($proposal_data->uid)->mail,
        '#title' => t('Email')
    );
    $form['contact_ph'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->contact_ph,
        '#title' => t('Contact No.')
    );
    $form['department'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->department,
        '#title' => t('Department/Branch')
    );
    $form['university'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->university,
        '#title' => t('University/Institute')
    );
    $form['lab_title'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->lab_title,
        '#title' => t('Title of the Lab')
    );
    /* get experiment details */
    $experiment_list = '<ul>';
    //$experiment_q = db_query("SELECT * FROM {lab_migration_experiment} WHERE proposal_id = %d ORDER BY id ASC", $proposal_id);
    $query = db_select('lab_migration_experiment');
    $query->fields('lab_migration_experiment');
    $query->condition('proposal_id', $proposal_id);
    $query->orderBy('id', 'ASC');
    $experiment_q = $query->execute();
    while ($experiment_data = $experiment_q->fetchObject())
      {
        $experiment_list .= '<li>' . $experiment_data->title . '</li>Description of Experiment : ' . $experiment_data->description . '<br>';
      }
    $experiment_list .= '</ul>';
    $form['experiment'] = array(
        '#type' => 'item',
        '#markup' => $experiment_list,
        '#title' => t('Experiments')
    );
    if ($proposal_data->solution_provider_uid == 0)
      {
        $solution_provider = "User will not provide solution, we will have to provide solution";
      }
    else if ($proposal_data->solution_provider_uid == $proposal_data->uid)
      {
        $solution_provider = "Proposer will provide the solution of the lab";
      }
    else
      {
        $solution_provider_user_data = user_load($proposal_data->solution_provider_uid);
        if ($solution_provider_user_data)
            $solution_provider = "Solution will be provided by user " . l($solution_provider_user_data->name, 'user/' . $proposal_data->solution_provider_uid);
        else
            $solution_provider = "User does not exists";
      }
    $form['solution_provider_uid'] = array(
        '#type' => 'item',
        '#title' => t('Who will provide the solution'),
        '#markup' => $solution_provider
    );
    /*$form['solution_display'] = array(
    '#type' => 'item',
    '#title' => t('Display the solution on the www.dwsim.fossee.in website'),
    '#markup' => ($proposal_data->solution_display == 1) ? "Yes" : "No",
    );*/
    $proposal_status = '';
    switch ($proposal_data->approval_status)
    {
        case 0:
            $proposal_status = t('Pending');
            break;
        case 1:
            $proposal_status = t('Approved');
            break;
        case 2:
            $proposal_status = t('Dis-approved');
            break;
        case 3:
            $proposal_status = t('Completed');
            break;
        default:
            $proposal_status = t('Unkown');
            break;
    }
    $form['proposal_status'] = array(
        '#type' => 'item',
        '#markup' => $proposal_status,
        '#title' => t('Proposal Status')
    );
    if ($proposal_data->approval_status == 0)
      {
        $form['approve'] = array(
            '#type' => 'item',
            '#markup' => l('Click here', 'lab-migration/manage-proposal/approve/' . $proposal_id),
            '#title' => t('Approve')
        );
      }
    if ($proposal_data->approval_status == 1)
      {
        $form['completed'] = array(
            '#type' => 'checkbox',
            '#title' => t('Completed'),
            '#description' => t('Check if user has provided all experiment solutions.')
        );
      }
    if ($proposal_data->approval_status == 2)
      {
        $form['message'] = array(
            '#type' => 'item',
            '#markup' => $proposal_data->message,
            '#title' => t('Reason for disapproval')
        );
      }
    $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Submit')
    );
    $form['cancel'] = array(
        '#type' => 'markup',
        '#markup' => l(t('Cancel'), 'lab-migration/manage-proposal/all')
    );
    return $form;
  }
function lab_migration_proposal_status_form_submit($form, &$form_state)
  {
    global $user;
    /* get current proposal */
    $proposal_id = (int) arg(3);
    //$proposal_q = db_query("SELECT * FROM {lab_migration_proposal} WHERE id = %d", $proposal_id);
    $query = db_select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q)
      {
        if ($proposal_data = $proposal_q->fetchObject())
          {
            /* everything ok */
          }
        else
          {
            drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
            drupal_goto('lab-migration/manage-proposal');
            return;
          }
      }
    else
      {
        drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
        drupal_goto('lab-migration/manage-proposal');
        return;
      }
    /* set the book status to completed */
    if ($form_state['values']['completed'] == 1)
      {
        $up_query = "UPDATE lab_migration_proposal SET approval_status = :approval_status , expected_completion_date = :expected_completion_date WHERE id = :proposal_id";
        $args = array(
            ":approval_status" => '3',
            ":proposal_id" => $proposal_id,
            ":expected_completion_date" => time()
        );
        $result = db_query($up_query, $args);
        CreateReadmeFileLabMigration($proposal_id);
        if (!$result)
          {
            drupal_set_message('Error in update status', 'error');
            return;
          }
        /* sending email */
        $user_data = user_load($proposal_data->uid);
        $email_to = $user_data->mail;
        $from = variable_get('lab_migration_from_email', '');
        $bcc = $user->mail . ', ' . variable_get('lab_migration_emails', '');
        $cc = variable_get('lab_migration_cc_emails', '');
        $param['proposal_completed']['proposal_id'] = $proposal_id;
        $param['proposal_completed']['user_id'] = $proposal_data->uid;
        $param['proposal_completed']['headers'] = array(
            'From' => $from,
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
            'Content-Transfer-Encoding' => '8Bit',
            'X-Mailer' => 'Drupal',
            'Cc' => $cc,
            'Bcc' => $bcc
        );
        if (!drupal_mail('lab_migration', 'proposal_completed', $email_to, language_default(), $param, $from, TRUE))
            drupal_set_message('Error sending email message.', 'error');
        /*$email_to = $user->mail . ', ' . variable_get('lab_migration_emails', '');;
        if (!drupal_mail('lab_migration', 'proposal_completed', $email_to , language_default(), $param, variable_get('lab_migration_from_email', NULL), TRUE))
        drupal_set_message('Error sending email message.', 'error');*/
        drupal_set_message('Congratulations! Lab Migration proposal has been marked as completed. User has been notified of the completion.', 'status');
      }
    drupal_goto('lab-migration/manage-proposal');
    return;
  }
/******************************************************************************/
/**************************** PROPOSAL EDIT FORM ******************************/
/******************************************************************************/
function lab_migration_proposal_edit_form($form, $form_state)
  {
    global $user;
    /* get current proposal */
    $proposal_id = (int) arg(3);
    //$proposal_q = db_query("SELECT * FROM {lab_migration_proposal} WHERE id = %d", $proposal_id);
    $query = db_select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q)
      {
        if ($proposal_data = $proposal_q->fetchObject())
          {
            /* everything ok */
          }
        else
          {
            drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
            drupal_goto('lab-migration/manage-proposal');
            return;
          }
      }
    else
      {
        drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
        drupal_goto('lab-migration/manage-proposal');
        return;
      }
    $user_data = user_load($proposal_data->uid);
    $form['name_title'] = array(
        '#type' => 'select',
        '#title' => t('Title'),
        '#options' => array(
            'Mr' => 'Mr',
            'Ms' => 'Ms',
            'Mrs' => 'Mrs',
            'Dr' => 'Dr',
            'Prof' => 'Prof'
        ),
        '#required' => TRUE,
        '#default_value' => $proposal_data->name_title
    );
    $form['name'] = array(
        '#type' => 'textfield',
        '#title' => t('Name of the Proposer'),
        '#size' => 30,
        '#maxlength' => 50,
        '#required' => TRUE,
        '#default_value' => $proposal_data->name
    );
    $form['email_id'] = array(
        '#type' => 'item',
        '#title' => t('Email'),
        '#markup' => $user_data->mail
    );
    $form['contact_ph'] = array(
        '#type' => 'textfield',
        '#title' => t('Contact No.'),
        '#size' => 30,
        '#maxlength' => 15,
        '#required' => TRUE,
        '#default_value' => $proposal_data->contact_ph
    );
    $form['department'] = array(
        '#type' => 'select',
        '#title' => t('Department/Branch'),
        '#options' => _lm_list_of_departments(),
        '#required' => TRUE,
        '#default_value' => $proposal_data->department
    );
    $form['university'] = array(
        '#type' => 'textfield',
        '#title' => t('University/Institute'),
        '#size' => 30,
        '#maxlength' => 50,
        '#required' => TRUE,
        '#default_value' => $proposal_data->university
    );
    $form['country'] = array(
        '#type' => 'select',
        '#title' => t('Country'),
        '#options' => array(
            'India' => 'India',
            'Others' => 'Others'
        ),
        '#default_value' => $proposal_data->country,
        '#required' => TRUE,
        '#tree' => TRUE,
        '#validated' => TRUE
    );
    $form['other_country'] = array(
        '#type' => 'textfield',
        '#title' => t('Other than India'),
        '#size' => 100,
        '#default_value' => $proposal_data->country,
        '#attributes' => array(
            'placeholder' => t('Enter your country name')
        ),
        '#states' => array(
            'visible' => array(
                ':input[name="country"]' => array(
                    'value' => 'Others'
                )
            )
        )
    );
    $form['other_state'] = array(
        '#type' => 'textfield',
        '#title' => t('State other than India'),
        '#size' => 100,
        '#attributes' => array(
            'placeholder' => t('Enter your state/region name')
        ),
        '#default_value' => $proposal_data->state,
        '#states' => array(
            'visible' => array(
                ':input[name="country"]' => array(
                    'value' => 'Others'
                )
            )
        )
    );
    $form['other_city'] = array(
        '#type' => 'textfield',
        '#title' => t('City other than India'),
        '#size' => 100,
        '#attributes' => array(
            'placeholder' => t('Enter your city name')
        ),
        '#default_value' => $proposal_data->city,
        '#states' => array(
            'visible' => array(
                ':input[name="country"]' => array(
                    'value' => 'Others'
                )
            )
        )
    );
    $form['all_state'] = array(
        '#type' => 'select',
        '#title' => t('State'),
        '#options' => _lm_list_of_states(),
        '#default_value' => $proposal_data->state,
        '#validated' => TRUE,
        '#states' => array(
            'visible' => array(
                ':input[name="country"]' => array(
                    'value' => 'India'
                )
            )
        )
    );
    $form['city'] = array(
        '#type' => 'select',
        '#title' => t('City'),
        '#options' => _lm_list_of_cities(),
        '#default_value' => $proposal_data->city,
        '#states' => array(
            'visible' => array(
                ':input[name="country"]' => array(
                    'value' => 'India'
                )
            )
        )
    );
    $form['pincode'] = array(
        '#type' => 'textfield',
        '#title' => t('Pincode'),
        '#size' => 30,
        '#maxlength' => 6,
        '#default_value' => $proposal_data->pincode,
        '#attributes' => array(
            'placeholder' => 'Insert pincode of your city/ village....'
        )
    );
    $form['lab_title'] = array(
        '#type' => 'textfield',
        '#title' => t('Title of the Lab'),
        '#size' => 30,
        '#maxlength' => 50,
        '#required' => TRUE,
        '#default_value' => $proposal_data->lab_title
    );
    /* get experiment details */
    // $experiment_q = db_query("SELECT * FROM {lab_migration_experiment} WHERE proposal_id = %d ORDER BY id ASC", $proposal_id);
    $query = db_select('lab_migration_experiment');
    $query->fields('lab_migration_experiment');
    $query->condition('proposal_id', $proposal_id);
    $query->orderBy('id', 'ASC');
    $experiment_q = $query->execute();
    $experiment_q_count = $experiment_q->rowCount();
    /*$form['lab_experiment'] = array(
    '#type' => 'fieldset',
    '#collapsible' => FALSE,
    '#tree' => TRUE,
    );*/
    for ($counter = 1; $counter <= 15; $counter++)
      {
        $experiment_title = '';
        $experiment_data = $experiment_q->fetchObject();
        if ($experiment_data)
          {
            $experiment_title = $experiment_data->title;
            $experiment_description = $experiment_data->description;
            /*$form['lab_experiment_']['update'][$experiment_data->id] = array(
            '#type' => 'textfield',
            '#title' => t('Title of the Experiment ') . $counter,
            '#size' => 50,
            '#required' => FALSE,
            
            '#default_value' => $experiment_title,
            );
            $form['lab_experiment']['update1'][$experiment_data->id] = array(
            '#type' => 'textarea',
            
            '#title' => t('Description for Experiment ') . $counter,
            '#default_value' => $experiment_description,
            );*/
            $form['lab_experiment_update' . $experiment_data->id] = array(
                '#type' => 'textfield',
                '#title' => t('Title of the Experiment ') . $counter,
                '#size' => 50,
                '#default_value' => $experiment_title
            );
            $namefield = "lab_experiment_update" . $experiment_data->id;
            $form['lab_experiment_description_update' . $experiment_data->id] = array(
                '#type' => 'textarea',
                '#attributes' => array(
                    'placeholder' => t('Enter Description for your experiment ' . $counter)
                ),
                '#default_value' => $experiment_description,
                '#title' => t('Description for Experiment ') . $counter
            );
          }
        else
          {
            $form['lab_experiment_insert' . $counter] = array(
                '#type' => 'textfield',
                '#title' => t('Title of the Experiment ') . $counter,
                '#size' => 50,
                '#required' => FALSE
            );
            $namefield = "lab_experiment_insert" . $counter;
            $form['lab_experiment_description_insert' . $counter] = array(
                '#type' => 'textarea',
                '#attributes' => array(
                    'placeholder' => t('Enter Description for your experiment ' . $counter)
                ),
                '#title' => t('Description for Experiment ') . $counter,
                '#states' => array(
                    'invisible' => array(
                        ':input[name=' . $namefield . ']' => array(
                            'value' => ""
                        )
                    )
                )
            );
          }
      }
    if ($proposal_data->solution_provider_uid == 0)
      {
        $solution_provider_user = 'Open';
      }
    else if ($proposal_data->solution_provider_uid == $proposal_data->uid)
      {
        $solution_provider_user = 'Proposer';
      }
    else
      {
        $user_data = user_load($proposal_data->solution_provider_uid);
        if (!$user_data)
          {
            $solution_provider_user = 1;
            drupal_set_message('Solution provider user name is invalid', 'error');
          }
        $solution_provider_user = $user_data->name;
      }
    $form['solution_provider_uid'] = array(
        '#type' => 'item',
        '#title' => t('Who will provide the solution'),
        '#markup' => $solution_provider_user
    );
    $form['open_solution'] = array(
        '#type' => 'checkbox',
        '#title' => t('Open the solution for everyone')
    );
    $form['solution_display'] = array(
        '#type' => 'hidden',
        '#title' => t('Do you want to display the solution on the www.cfd.fossee.in website'),
        '#options' => array(
            '1' => 'Yes'
        ),
        '#required' => TRUE,
        // '#default_value' => ($proposal_data->solution_display == 1) ? "1" : "2",
        '#default_value' => '1'
    );
    $form['delete_proposal'] = array(
        '#type' => 'checkbox',
        '#title' => t('Delete Proposal')
    );
    $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Submit')
    );
    $form['cancel'] = array(
        '#type' => 'item',
        '#markup' => l(t('Cancel'), 'lab-migration/manage-proposal')
    );
    return $form;
  }
function lab_migration_proposal_edit_form_validate($form, &$form_state)
  {
    $proposal_id = (int) arg(3);
    /* check before delete proposal */
    if ($form_state['values']['delete_proposal'] == 1)
      {
        //$experiment_q = db_query("SELECT * FROM {lab_migration_experiment} WHERE proposal_id = %d", $proposal_id);
        $query = db_select('lab_migration_experiment');
        $query->fields('lab_migration_experiment');
        $query->condition('proposal_id', $proposal_id);
        $experiment_q = $query->execute();
        while ($experiment_data = $experiment_q->fetchObject())
          {
            //$solution_q = db_query("SELECT * FROM {lab_migration_solution} WHERE experiment_id = %d", $experiment_data->id);
            $query = db_select('lab_migration_solution');
            $query->fields('lab_migration_solution');
            $query->condition('experiment_id', $experiment_data->id);
            $solution_q = $query->execute();
            if ($solution_q->fetchObject())
              {
                form_set_error('', t('Cannot delete proposal since there are solutions already uploaded. Use the "Bulk Manage" interface to delete this proposal'));
              }
          }
      }
    return;
  }
function lab_migration_proposal_edit_form_submit($form, &$form_state)
  {
    global $user;
    /* get current proposal */
    $proposal_id = (int) arg(3);
    // $proposal_q = db_query("SELECT * FROM {lab_migration_proposal} WHERE id = %d", $proposal_id);
    $query = db_select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q)
      {
        if ($proposal_data = $proposal_q->fetchObject())
          {
            /* everything ok */
          }
        else
          {
            drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
            drupal_goto('lab-migration/manage-proposal');
            return;
          }
      }
    else
      {
        drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
        drupal_goto('lab-migration/manage-proposal');
        return;
      }
    /* delete proposal */
    if ($form_state['values']['delete_proposal'] == 1)
      {
        //db_query("DELETE FROM {lab_migration_proposal} WHERE id = %d", $proposal_id);
        $query = db_delete('lab_migration_proposal');
        $query->condition('id', $proposal_id);
        $num_deleted = $query->execute();
        //db_query("DELETE FROM {lab_migration_experiment} WHERE proposal_id = %d", $proposal_id);
        $query = db_delete('lab_migration_experiment');
        $query->condition('proposal_id', $proposal_id);
        $num_deleted = $query->execute();
        drupal_set_message(t('Proposal Delete'), 'status');
        drupal_goto('lab-migration/manage-proposal');
        return;
      }
    if ($form_state['values']['open_solution'] == 1)
      {
        // $query = "UPDATE {lab_migration_proposal} SET solution_provider_uid = :solution_provider_uid, solution_status = :solution_status, solution_provider_name_title = '', solution_provider_name = '', solution_provider_contact_ph = '', solution_provider_department = '', solution_provider_university = '' WHERE id = :proposal_id";
        // $args= array(
        //    ":solution_provider_uid" => 0, 
        //    ":solution_status" => 0,
        //    ":proposal_id" => $proposal_id,
        // );
        // $result = db_query($query, $args);
        $result = db_update('lab_migration_proposal')->fields(array(
            'solution_provider_uid' => 0,
            'solution_status' => 0,
            'solution_provider_name_title' => '',
            'solution_provider_name' => '',
            'solution_provider_contact_ph' => '',
            'solution_provider_department' => '',
            'solution_provider_university' => ''
        ))->condition('id', $proposal_id)->execute();
        if (!$result)
          {
            drupal_set_message(t('Solution already open for everyone.'), 'error');
            return;
          }
      }
    $solution_display = 0;
    if ($form_state['values']['solution_display'] == 1)
      {
        $solution_display = 1;
      }
    else
      {
        $solution_display = 0;
      }
    /* update proposal */
    $v = $form_state['values'];
    //$query = "UPDATE {lab_migration_proposal} SET name_title = :name_title, name = :name, contact_ph = :contact_ph, department = :department, university = :unversity, lab_title = :lab_title, solution_display = :solution_display WHERE id = :id";
    // $args= array(    
    //    ":name_title" => $v['name_title'],
    //    ":name" => $v['name'],
    //    "contact_ph" => $v['contact_ph'],
    //    ":department" => $v['department'],
    //    ":university" => $v['university'],
    //    ":lab_title" => $v['lab_title'],
    //    ":solution_display" => $solution_display,
    //    ":id" => $proposal_id,
    //  );
    $lab_title = $v['lab_title'];
    $proposar_name = $v['name_title'] . ' ' . $v['name'];
    $university = $v['university'];
    $directory_names = _lm_dir_name($lab_title, $proposar_name, $university);
    if (LM_RenameDir($proposal_id, $directory_names))
      {
        $directory_name = $directory_names;
      }
    else
      {
        return;
      }
    $query = db_update('lab_migration_proposal')->fields(array(
        'name_title' => $v['name_title'],
        'name' => $v['name'],
        'contact_ph' => $v['contact_ph'],
        'department' => $v['department'],
        'university' => $v['university'],
        'city' => $v['city'],
        'pincode' => $v['pincode'],
        'state' => $v['all_state'],
        'lab_title' => $v['lab_title'],
        'solution_display' => $solution_display,
        'directory_name' => $directory_name
    ))->condition('id', $proposal_id);
    $result1 = $query->execute();
    //$result=db_query($query, $args);
    /* updating existing experiments */
    $query = db_select('lab_migration_experiment');
    $query->fields('lab_migration_experiment');
    $query->condition('proposal_id', $proposal_id);
    $query->orderBy('id', 'ASC');
    $experiment_q = $query->execute();
    for ($counter = 1; $counter <= 15; $counter++)
      {
        $experiment_data = $experiment_q->fetchObject();
        if ($experiment_data)
          {
            $experiment_field_name = 'lab_experiment_update' . $experiment_data->id;
            $experiment_description = 'lab_experiment_description_update' . $experiment_data->id;
            if (strlen(trim($form_state['values'][$experiment_field_name])) >= 1)
              {
                $query = "UPDATE {lab_migration_experiment} SET title = :title, description= :description WHERE id = :id";
                $args = array(
                    ":title" => trim($form_state['values'][$experiment_field_name]),
                    ":description" => trim($form_state['values'][$experiment_description]),
                    ":id" => $experiment_data->id
                );
                $result2 = db_query($query, $args);
                if (!$result2)
                  {
                    drupal_set_message(t('Could not update Title of the Experiment : ') . trim($form_state['values'][$experiment_field_name]), 'error');
                  }
              }
            else
              {
                $query = "DELETE FROM {lab_migration_experiment} WHERE id = :id LIMIT 1";
                $args = array(
                    ":id" => $experiment_data->id
                );
                $result3 = db_query($query, $args);
              }
          }
      }
    /* foreach ($form_state['values']['lab_experiment']['update'] as $update_id => $update_value) {
    if (strlen(trim($update_value)) >= 1) {
    $description= $form_state['values']['lab_experiment_description']['update']; 
    $query = "UPDATE {lab_migration_experiment} SET title = :title and description=:description WHERE id = :id";
    $args = array(
    ":title"=>  trim($update_value),
    ":description"=>trim($description),
    ":id"=> $update_id,
    );
    $result2 = db_query($query, $args);
    if (!$result2)
    {
    drupal_set_message(t('Could not update Title of the Experiment : ') . trim($update_value), 'error');
    }
    } else {
    $query = "DELETE FROM {lab_migration_experiment} WHERE id = :id LIMIT 1";
    $args = array( 
    ":id" => $update_id
    );
    $result3 = db_query($query, $args);
    }
    }*/
    /* inserting new experiments */
    $query = db_select('lab_migration_experiment');
    $query->fields('lab_migration_experiment');
    $query->condition('proposal_id', $proposal_id);
    $query->orderBy('number', 'DESC');
    $query->range(0, 1);
    $number_q = $query->execute();
    if ($number_data = $number_q->fetchObject())
      {
        $number = (int) $number_data->number;
        $number++;
      }
    else
      {
        $number = 1;
      }
    for ($counter = 1; $counter <= 15; $counter++)
      {
        $lab_experiment_insert = 'lab_experiment_insert' . $counter;
        //var_dump($form_state['values'][$lab_experiment_insert]);die;
        $lab_experiment_description_insert = 'lab_experiment_description_insert' . $counter;
        if (!empty($form_state['values'][$lab_experiment_insert]))
          {
            //var_dump($form_state['values'][$lab_experiment_insert]);die;
            $query = "INSERT INTO {lab_migration_experiment} (proposal_id, number, title, description) VALUES (:proposal_id, :number, :title, :description)";
            $args = array(
                ":proposal_id" => $proposal_id,
                ":number" => $number,
                ":title" => trim($form_state['values'][$lab_experiment_insert]),
                ":description" => trim($form_state['values'][$lab_experiment_description_insert])
            );
            $result4 = db_query($query, $args);
            if (!$result4)
              {
                drupal_set_message(t('Could not insert Title of the Experiment : ') . trim($form_state['values'][$lab_experiment_insert]), 'error');
              }
            else
              {
                $number++;
              }
          }
      }
    /* $query = db_select('lab_migration_experiment');
    $query->fields('lab_migration_experiment');
    $query->condition('proposal_id', $proposal_id);
    $query->orderBy('number', 'DESC');
    $query->range(0, 1);
    $number_q = $query->execute();
    if ($number_data = $number_q->fetchObject()) {
    $number = (int)$number_data->number;
    $number++;
    } else {
    $number = 1;
    }
    $insertvalue = array($insert_id => $insert_value);
    $lab_experimentinsert = $form_state['values']['lab_experiment']['insert'];
    $lab_exp_descriptioninsert=$form_state['values']['lab_experiment_description']['insert'];
    if (is_array($lab_experimentinsert) || is_object($lab_experimentinsert))
    {  
    foreach ($lab_experimentinsert as $insertvalue) {
    //foreach ($form_state['values']['lab_experiment']['insert'] as $insert_id => $insert_value) {
    if (strlen(trim($insert_value)) >= 1) {
    $query = "INSERT INTO {lab_migration_experiment} (proposal_id, number, title, description) VALUES :proposal_id, :number, :title, :description";
    $args = array(
    ":proposal_id" => $proposal_id, 
    ":number" => $number, 
    ":title" => trim($insert_value),
    ":description"=>""
    );
    $result4 = db_query($query, $args);
    if (!$result4)
    {
    drupal_set_message(t('Could not insert Title of the Experiment : ') . trim($insert_value), 'error');
    } else {
    $number++;
    }
    }
    }
    }*/
    drupal_set_message(t('Proposal Updated'), 'status');
  }
/******************************************************************************/
/**************************** CATEGORY EDIT FORM ******************************/
/******************************************************************************/
function lab_migration_category_edit_form($form, $form_state)
  {
    /* get current proposal */
    $proposal_id = (int) arg(4);
    //$proposal_q = db_query("SELECT * FROM {lab_migration_proposal} WHERE id = %d", $proposal_id);
    $query = db_select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q)
      {
        if ($proposal_data = $proposal_q->fetchObject())
          {
            /* everything ok */
          }
        else
          {
            drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
            drupal_goto('lab-migration/manage-proposal');
            return;
          }
      }
    else
      {
        drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
        drupal_goto('lab-migration/manage-proposal');
        return;
      }
    $form['name'] = array(
        '#type' => 'item',
        '#markup' => l($proposal_data->name_title . ' ' . $proposal_data->name, 'user/' . $proposal_data->uid),
        '#title' => t('Name')
    );
    $form['email_id'] = array(
        '#type' => 'item',
        '#markup' => user_load($proposal_data->uid)->mail,
        '#title' => t('Email')
    );
    $form['contact_ph'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->contact_ph,
        '#title' => t('Contact No.')
    );
    $form['department'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->department,
        '#title' => t('Department/Branch')
    );
    $form['university'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->university,
        '#title' => t('University/Institute')
    );
    $form['lab_title'] = array(
        '#type' => 'item',
        '#markup' => $proposal_data->lab_title,
        '#title' => t('Title of the Lab')
    );
    $form['category'] = array(
        '#type' => 'select',
        '#title' => t('Category'),
        '#options' => _lm_list_of_categories(),
        '#required' => TRUE,
        '#default_value' => $proposal_data->category
    );
    $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Submit')
    );
    $form['cancel'] = array(
        '#type' => 'item',
        '#markup' => l(t('Cancel'), 'lab-migration/manage-proposal/category')
    );
    return $form;
  }
function lab_migration_category_edit_form_submit($form, &$form_state)
  {
    /* get current proposal */
    $proposal_id = (int) arg(4);
    //$proposal_q = db_query("SELECT * FROM {lab_migration_proposal} WHERE id = %d", $proposal_id);
    $query = db_select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('id', $proposal_id);
    $proposal_q = $query->execute();
    if ($proposal_q)
      {
        if ($proposal_data = $proposal_q->fetchObject())
          {
            /* everything ok */
          }
        else
          {
            drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
            drupal_goto('lab-migration/manage-proposal');
            return;
          }
      }
    else
      {
        drupal_set_message(t('Invalid proposal selected. Please try again.'), 'error');
        drupal_goto('lab-migration/manage-proposal');
        return;
      }
    $query = "UPDATE {lab_migration_proposal} SET category = :category WHERE id = :proposal_id";
    $args = array(
        ":category" => $form_state['values']['category'],
        ":proposal_id" => $proposal_data->id
    );
    $result = db_query($query, $args);
    drupal_set_message(t('Proposal Category Updated'), 'status');
    drupal_goto('lab-migration/manage-proposal/category');
  }
function _lm_list_of_departments()
  {
    $department = array(
        0 => '-Select-'
    );
    $query = db_select('list_of_departments');
    $query->fields('list_of_departments');
    $query->orderBy('id', 'DESC');
    $department_list = $query->execute();
    while ($department_list_data = $department_list->fetchObject())
      {
        $department[$department_list_data->department] = $department_list_data->department;
      }
    return $department;
  }
function _lm_list_of_categories()
  {
    $category = array(
        0 => '-Select-'
    );
    $query = db_select('list_of_categories');
    $query->fields('list_of_categories');
    $query->orderBy('id', 'DESC');
    $category_list = $query->execute();
    while ($category_list_data = $category_list->fetchObject())
      {
        $category[$category_list_data->category] = $category_list_data->category;
      }
    return $category;
  }
function _lm_list_of_states()
  {
    $states = array(
        0 => '-Select-'
    );
    $query = db_select('list_states_of_india');
    $query->fields('list_states_of_india');
    //$query->orderBy('', '');
    $states_list = $query->execute();
    while ($states_list_data = $states_list->fetchObject())
      {
        $states[$states_list_data->state] = $states_list_data->state;
      }
    return $states;
  }
function _lm_list_of_cities()
  {
    $city = array();
    $query = db_select('list_cities_of_india');
    $query->fields('list_cities_of_india');
    $query->orderBy('city', 'ASC');
    $city_list = $query->execute();
    while ($city_list_data = $city_list->fetchObject())
      {
        $city[$city_list_data->city] = $city_list_data->city;
      }
    return $city;
  }
function CreateReadmeFileLabMigration($proposal_id)
  {
    $result = db_query("
                        SELECT * from lab_migration_proposal WHERE id = :proposal_id", array(
        ":proposal_id" => $proposal_id
    ));
    $proposal_data = $result->fetchObject();
    $root_path = lab_migration_path();
    $readme_file = fopen($root_path . $proposal_data->directory_name . "/README.txt", "w") or die("Unable to open file!");
    $txt = "";
    $txt .= "About the lab";
    $txt .= "\n" . "\n";
    $txt .= "Title Of The Lab: " . $proposal_data->lab_title . "\n";
    $txt .= "Proposar Name: " . $proposal_data->name_title . " " . $proposal_data->name . "\n";
    $txt .= "Department: " . $proposal_data->department . "\n";
    $txt .= "University: " . $proposal_data->university . "\n";
    $txt .= "Category: " . $proposal_data->department . "\n\n";
    $txt .= "\n" . "\n";
    $txt .= "Solution provider";
    $txt .= "\n" . "\n";
    $txt .= "Solution Provider Name: " . $proposal_data->solution_provider_name_title . " " . $proposal_data->solution_provider_name . "\n";
    $txt .= "Solution Provider University: " . $proposal_data->solution_provider_university . "\n";
    $txt .= "\n" . "\n";
    $txt .= "Lab Migration Project By FOSSEE, IIT Bombay" . "\n";
    fwrite($readme_file, $txt);
    fclose($readme_file);
    return $txt;
  }
function LM_RenameDir($proposal_id, $dir_name)
  {
    $proposal_id = $proposal_id;
    $dir_name = $dir_name;
    $query = db_query("SELECT directory_name,id FROM lab_migration_proposal WHERE id = :proposal_id", array(
        ':proposal_id' => $proposal_id
    ));
    $result = $query->fetchObject();
    if($result != NULL){
    $files = scandir(lab_migration_path());
    $files_id_dir = lab_migration_path() . $result->id;
//var_dump($files);die;
   $file_dir = lab_migration_path() . $result->directory_name;
if(is_dir($file_dir)){  
        $new_directory_name = rename(lab_migration_path() . $result->directory_name, lab_migration_path() . $dir_name);
return $new_directory_name;
} else if(is_dir($files_id_dir)) {
	$new_directory_name = rename(lab_migration_path() . $result->id, lab_migration_path() . $dir_name);
          return $new_directory_name;
	}else{
 drupal_set_message('Can not rename the directory. Please contact to administrator');
return;

}
     }else{
 drupal_set_message('Lab names directory not present in databse');
 return;         
}
    //var_dump($files);die;
   /* if ($files != NULL)
      {
        $new_directory_name = rename(lab_migration_path() . $result->directory_name, lab_migration_path() . $dir_name) or drupal_set_message("Unable to rename folder");
      }
    else
      {
        $new_directory_name = 'Can not rename the directory. Directory not present';
      }*/
    return ;
  }
function _lm_dir_name($lab, $name, $university)
  {
    $lab_title = ucname($lab);
    $proposar_name = ucname($name);
    $university_name = ucname($university);
    $dir_name = $lab_title . " " . "by" . " " . $proposar_name . ' ' . $university_name;
    $directory_name = str_replace("__", "_", str_replace(" ", "_", $dir_name));
    return $directory_name;
  }
