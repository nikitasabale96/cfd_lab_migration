<?php
// $Id$
/*
Approval Status :
0 - Pending
1 - Approved
2 - Dis-Approved
3 - Solved
Solution Status :
0 - Pending
1 - Approved
2 - Dis-Approved
Solution Display :
0 - No
1 - Yes

Tables :
lab_migration_solution : approval_status
0 - Pending
1 - Approved
2 - Disapproved (delete it)
*/
function lab_migration_proposal_form($form, &$form_state)
  {
    global $user;
    /************************ start approve book details ************************/
    if ($user->uid == 0) {
        $msg =  drupal_set_message(t('It is mandatory to ' . l('login', 'user') . ' on this website to access the lab proposal form. If you are new user please create a new account first.'), 'error');
         //drupal_goto('lab-migration-project');
         drupal_goto('user');
         return $msg;
    }
    $query = db_select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('uid', $user->uid);
    $query->orderBy('id', 'DESC');
    $query->range(0, 1);
    $proposal_q = $query->execute();
    $proposal_data = $proposal_q->fetchObject();
    if ($proposal_data)
      {
        if ($proposal_data->approval_status == 0 || $proposal_data->approval_status == 1)
          {
            drupal_set_message(t('We have already received your proposal.'), 'status');
            drupal_goto('');
            return;
          }
      }
    $form['#attributes'] = array(
        'enctype' => "multipart/form-data"
    );
    $form['name_title'] = array(
        '#type' => 'select',
        '#title' => t('Title'),
        '#options' => array(
            'Dr' => 'Dr',
            'Prof' => 'Prof'
        ),
        '#required' => TRUE
    );
    $form['name'] = array(
        '#type' => 'textfield',
        '#title' => t('Name of the Proposer'),
        '#size' => 100,
        '#attributes' => array(
            'placeholder' => t('Enter your full name')
        ),
        '#maxlength' => 100,
        '#required' => TRUE
    );
    $form['email_id'] = array(
        '#type' => 'textfield',
        '#title' => t('Email'),
        '#size' => 30,
        '#value' => $user->mail,
        '#disabled' => TRUE
    );
    $form['contact_ph'] = array(
        '#type' => 'textfield',
        '#title' => t('Contact No.'),
        '#size' => 30,
        '#attributes' => array(
            'placeholder' => t('Enter your contact number')
        ),
        '#maxlength' => 15,
        '#required' => TRUE
    );
    $form['department'] = array(
        '#type' => 'select',
        '#title' => t('Department/Branch'),
        '#options' => _lm_list_of_departments(),
        '#required' => TRUE
    );
    $form['university'] = array(
        '#type' => 'textfield',
        '#title' => t('University/ Institute'),
        '#size' => 80,
        '#maxlength' => 200,
        '#required' => TRUE,
        '#attributes' => array(
            'placeholder' => 'Insert full name of your institute/ university.... '
        )
    );
    $form['country'] = array(
        '#type' => 'select',
        '#title' => t('Country'),
        '#options' => array(
            'India' => 'India',
            'Others' => 'Others'
        ),
        '#required' => TRUE,
        '#tree' => TRUE,
        '#validated' => TRUE
    );
    $form['other_country'] = array(
        '#type' => 'textfield',
        '#title' => t('Other than India'),
        '#size' => 100,
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
        '#required' => False,
        '#attributes' => array(
            'placeholder' => 'Enter pincode....'
        )
    );
    /***************************************************************************/
    $form['hr'] = array(
        '#type' => 'item',
        '#markup' => '<hr>'
    );
    $form['version'] = array(
        '#type' => 'hidden',
        
        '#value' => 'Not available',
        
    );
    /*$form['version'] = array(
        '#type' => 'select',
        '#title' => t('Version'),
        '#options' => _lm_list_of_software_version(),
        '#required' => TRUE
    );
    $form['older'] = array(
        '#type' => 'textfield',
        '#size' => 30,
        '#maxlength' => 50,
        //'#required' => TRUE,
        '#description' => t('Specify the Older version used'),
        '#states' => array(
            'visible' => array(
                ':input[name="version"]' => array(
                    'value' => 'olderversion'
                )
            )
        )
    );*/
    $form['lab_title'] = array(
        '#type' => 'textfield',
        '#title' => t('Title of the Lab'),
        '#size' => 50,
        '#required' => TRUE
    );
    $first_experiemnt = TRUE;
    for ($counter = 1; $counter <= 15; $counter++)
      {
        if ($counter <= 5)
          {
            $form['lab_experiment-' . $counter] = array(
                '#type' => 'textfield',
                '#title' => t('Title of the Experiment ') . $counter,
                '#size' => 50,
            );
            $namefield = "lab_experiment-" . $counter;
            $form['lab_experiment_description-' . $counter] = array(
                '#type' => 'textarea',
                '#attributes' => array(
                    'placeholder' => t('Enter Description for your experiment ' . $counter),
                    'cols' => 50,
                    'rows' => 4
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
        else
          {
            $form['lab_experiment-' . $counter] = array(
                '#type' => 'textfield',
                '#title' => t('Title of the Experiment ') . $counter,
                '#size' => 50,
                '#required' => FALSE
            );
            $namefield = "lab_experiment-" . $counter;
            $form['lab_experiment_description-' . $counter] = array(
                '#type' => 'textarea',
                '#required' => FALSE,
                '#attributes' => array(
                    'placeholder' => t('Enter Description for your experiment ' . $counter),
                    'cols' => 50,
                    'rows' => 4
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
        $first_experiemnt = FALSE;
      }
    $form['solution_provider_uid'] = array(
        '#type' => 'radios',
        '#title' => t('Do you want to provide the solution'),
        '#options' => array(
            '1' => 'Yes',
            '2' => 'No'
        ),
        '#required' => TRUE,
        '#default_value' => '1',
        '#description' => 'If you dont want to provide the solution then it will be opened for the community, anyone may come forward and provide the solution.'
    );
    $form['solution_display'] = array(
        '#type' => 'hidden',
        '#title' => t('Do you want to display the solution on the www.cfd.fossee.in website'),
        '#options' => array(
            '1' => 'Yes'
        ),
        '#required' => TRUE,
        '#default_value' => '1',
        '#description' => 'If yes, solutions will be made available to everyone for downloading.',
        '#disabled' => FALSE
    );
    $form['submit'] = array(
        '#type' => 'submit',
        '#value' => t('Submit')
    );
    return $form;
  }
function lab_migration_proposal_form_validate($form, &$form_state)
  {
    if (!preg_match('/^[0-9\ \+]{0,15}$/', $form_state['values']['contact_ph']))
      {
        form_set_error('contact_ph', t('Invalid contact phone number'));
      }
    if ($form_state['values']['country'] == 'Others')
      {
        if ($form_state['values']['other_country'] == '')
          {
            form_set_error('other_country', t('Enter country name'));
            // $form_state['values']['country'] = $form_state['values']['other_country'];
          }
        else
          {
            $form_state['values']['country'] = $form_state['values']['other_country'];
          }
        if ($form_state['values']['other_state'] == '')
          {
            form_set_error('other_state', t('Enter state name'));
            // $form_state['values']['country'] = $form_state['values']['other_country'];
          }
        else
          {
            $form_state['values']['all_state'] = $form_state['values']['other_state'];
          }
        if ($form_state['values']['other_city'] == '')
          {
            form_set_error('other_city', t('Enter city name'));
            // $form_state['values']['country'] = $form_state['values']['other_country'];
          }
        else
          {
            $form_state['values']['city'] = $form_state['values']['other_city'];
          }
      }
    else
      {
        if ($form_state['values']['country'] == '')
          {
            form_set_error('country', t('Select country name'));
            // $form_state['values']['country'] = $form_state['values']['other_country'];
          }
        if ($form_state['values']['all_state'] == '')
          {
            form_set_error('all_state', t('Select state name'));
            // $form_state['values']['country'] = $form_state['values']['other_country'];
          }
        if ($form_state['values']['city'] == '')
          {
            form_set_error('city', t('Select city name'));
            // $form_state['values']['country'] = $form_state['values']['other_country'];
          }
      }
    for ($counter = 1; $counter <= 15; $counter++)
      {
        $experiment_field_name = 'lab_experiment-' . $counter;
        $experiment_description = 'lab_experiment_description-' . $counter;
        if (strlen(trim($form_state['values'][$experiment_field_name])) >= 1)
          {
            if (strlen(trim($form_state['values'][$experiment_description])) <= 49)
              {
                form_set_error($experiment_description, t('Description should be minimum of 50 characters'));
              }
          }
      }
    if ($form_state['values']['version'] == 'olderversion')
      {
        if ($form_state['values']['older'] == '')
          {
            form_set_error('older', t('Please provide valid version'));
          }
      }
    return;
  }
function lab_migration_proposal_form_submit($form, &$form_state)
  {
    global $user;
    if (!$user->uid)
      {
        drupal_set_message('It is mandatory to login on this website to access the proposal form', 'error');
        return;
      }
    $solution_provider_uid = 0;
    $solution_status = 0;
    $solution_provider_name_title = '';
    $solution_provider_name = '';
    $solution_provider_contact_ph = '';
    $solution_provider_department = '';
    $solution_provider_university = '';
    $solution_provider_city = '';
    $solution_provider_country = '';
    $solution_provider_state = '';
    $solution_provider_pincode = '';
    if ($form_state['values']['solution_provider_uid'] == "1")
      {
        $solution_provider_uid = $user->uid;
        $solution_status = 1;
        $solution_provider_name_title = $form_state['values']['name_title'];
        $solution_provider_name = $form_state['values']['name'];
        $solution_provider_contact_ph = $form_state['values']['contact_ph'];
        $solution_provider_department = $form_state['values']['department'];
        $solution_provider_university = $form_state['values']['university'];
        $solution_provider_city = $form_state['values']['city'];
        $solution_provider_country = $form_state['values']['country'];
        $solution_provider_state = $form_state['values']['all_state'];
        $solution_provider_pincode = $form_state['values']['pincode'];
      }
    else
      {
        $solution_provider_uid = 0;
      }
    $solution_display = 0;
    if ($form_state['values']['solution_display'] == "1")
        $solution_display = 1;
    else
        $solution_display = 1;
    if ($form_state['values']['version'] == 'olderversion')
      {
        $form_state['values']['version'] = $form_state['values']['older'];
      }
    /* inserting the user proposal */
    $v = $form_state["values"];
    $lab_title = $v['lab_title'];
    $proposar_name = $v['name_title']. ' ' . $v['name'];
    $university = $v['university'];    
    $directory_name = _lm_dir_name($lab_title, $proposar_name, $university);
    $result = "INSERT INTO {lab_migration_proposal} 
    (uid, approver_uid, name_title, name, contact_ph, department, university, city, pincode, state, country, version, lab_title, approval_status, solution_status, solution_provider_uid, solution_display, creation_date, approval_date, solution_date, solution_provider_name_title, solution_provider_name, solution_provider_contact_ph, solution_provider_department, solution_provider_university, solution_provider_city, solution_provider_country, solution_provider_state, solution_provider_pincode, directory_name) VALUES
    (:uid, :approver_uid, :name_title, :name, :contact_ph, :department, :university, :city, :pincode, :state, :country,
     :version, :lab_title, :approval_status, :solution_status, :solution_provider_uid, :solution_display, :creation_date, 
     :approval_date, :solution_date, :solution_provider_name_title, :solution_provider_name,
      :solution_provider_contact_ph, :solution_provider_department, :solution_provider_university, :solution_provider_city, :solution_provider_country, :solution_provider_state, :solution_provider_pincode, :directory_name)";
    $args = array(
        ":uid" => $user->uid,
        ":approver_uid" => 0,
        ":name_title" => $v['name_title'],
        ":name" => $v['name'],
        ":contact_ph" => $v['contact_ph'],
        ":department" => $v['department'],
        ":university" => $v['university'],
        ":city" => $v['city'],
        ":pincode" => $v['pincode'],
        ":state" => $v['all_state'],
        ":country" => $v['country'],
        ":version" => $form_state['values']['version'],
        ":lab_title" => $v['lab_title'],
        ":approval_status" => 0,
        ":solution_status" => $solution_status,
        ":solution_provider_uid" => $solution_provider_uid,
        ":solution_display" => $solution_display,
        ":creation_date" => time(),
        ":approval_date" => 0,
        ":solution_date" => 0,
        ":solution_provider_name_title" => $solution_provider_name_title,
        ":solution_provider_name" => $solution_provider_name,
        ":solution_provider_contact_ph" => $solution_provider_contact_ph,
        ":solution_provider_department" => $solution_provider_department,
        ":solution_provider_university" => $solution_provider_university,
        ":solution_provider_city" => $solution_provider_city,
        ":solution_provider_country" => $solution_provider_country,
        ":solution_provider_state" => $solution_provider_state,
        ":solution_provider_pincode" => $solution_provider_pincode,
        ":directory_name" => $directory_name
    );
    $proposal_id = db_query($result, $args, array(
        'return' => Database::RETURN_INSERT_ID
    ));
    if (!$proposal_id)
      {
        drupal_set_message(t('Error receiving your proposal. Please try again.'), 'error');
        return;
      }
    /* proposal id */
    //$proposal_id = db_last_insert_id('lab_migration_proposal', 'id');
    /* adding experiments */
    $number = 1;
    for ($counter = 1; $counter <= 15; $counter++)
      {
        $experiment_field_name = 'lab_experiment-' . $counter;
        $experiment_description = 'lab_experiment_description-' . $counter;
        if (strlen(trim($form_state['values'][$experiment_field_name])) >= 1)
          {
            //$query = "INSERT INTO {lab_migration_experiment} (proposal_id, directory_name, number, title,description) VALUES (:proposal_id, :directory_name, :number, :experiment_field_name,:description)";
             $query = "INSERT INTO {lab_migration_experiment} (proposal_id, number, title,description) VALUES (:proposal_id, :number, :experiment_field_name,:description)";
            $args = array(
                ":proposal_id" => $proposal_id,
               // ":directory_name" => $directory_name,
                ":number" => $number,
                ":experiment_field_name" => trim($form_state['values'][$experiment_field_name]),
                ":description" => trim($form_state['values'][$experiment_description])
            );
            $result = db_query($query, $args);
            if (!$result)
              {
                drupal_set_message(t('Could not insert Title of the Experiment : ') . trim($form_state['values'][$experiment_field_name]), 'error');
              }
            else
              {
                $number++;
              }
          }
      }
    /* sending email */
    $email_to = $user->mail;
    $from = variable_get('lab_migration_from_email', '');
    $bcc = variable_get('lab_migration_emails', '');
    $cc = variable_get('lab_migration_cc_emails', '');
    $param['proposal_received']['proposal_id'] = $proposal_id;
    $param['proposal_received']['user_id'] = $user->uid;
    $param['proposal_received']['headers'] = array(
        'From' => $from,
        'MIME-Version' => '1.0',
        'Content-Type' => 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
        'Content-Transfer-Encoding' => '8Bit',
        'X-Mailer' => 'Drupal',
        'Cc' => $cc,
        'Bcc' => $bcc
    );
    if (!drupal_mail('lab_migration', 'proposal_received', $email_to, user_preferred_language($user), $param, $from, TRUE))
        drupal_set_message('Error sending email message.', 'error');
    drupal_set_message(t('We have received you Lab migration proposal. We will get back to you soon.'), 'status');
    drupal_goto('');
  }
