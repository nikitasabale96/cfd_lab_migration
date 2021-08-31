<?php

/******************************************************************************/
/****************************** DELETION FUNCTIONS ****************************/
/******************************************************************************/
function lab_migration_delete_solution($solution_id)
{
    global $user;
    $root_path = lab_migration_path();
    $status = TRUE;
    $query = db_select('lab_migration_solution');
    $query->fields('lab_migration_solution');
    $query->condition('id', $solution_id);
    $solution_q = $query->execute();
    $solution_data = $solution_q->fetchObject();
    if (!$solution_data)
      {
        drupal_set_message(t('Invalid solution.'), 'error');
        return FALSE;
      }
    $query = db_select('lab_migration_experiment');
    $query->fields('lab_migration_experiment');
    $query->condition('id', $solution_data->experiment_id);
    $experiment_q = $query->execute();
    $experiment_data = $experiment_q->fetchObject();
    $query = db_select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('id', $experiment_data->proposal_id);
    $proposal_q = $query->execute();
    $proposal_data = $proposal_q->fetchObject();
    if (!$experiment_data)
    {
        drupal_set_message(t('Invalid experiment.'), 'error');
        return FALSE;
    }
  /* deleting solution files */
    $query = db_select('lab_migration_solution_files');
    $query->fields('lab_migration_solution_files');
    $query->condition('solution_id', $solution_id);
    $solution_files_q = $query->execute();
    while ($solution_files_data = $solution_files_q->fetchObject())
    {
        $ex_path = $proposal_data->directory_name . '/' . $solution_files_data->filepath;
        $dir_path = $root_path . $ex_path;
        if (!file_exists($dir_path))
        {
          $status = FALSE;
          drupal_set_message(t('Error deleting !file. File does not exists.', array('!file' => $dir_path)), 'error');
          continue;
        }
    /* removing solution file */
        if (!unlink($dir_path))
        {
          $status = FALSE;
          drupal_set_message(t('Error deleting !file', array('!file' => $dir_path)), 'error');

          /* sending email to admins */
            $email_to = variable_get('lab_migration_emails', '');
            $from = variable_get('lab_migration_from_email', '');
            $bcc="";
            $cc=variable_get('lab_migration_cc_emails', '');
            $param['standard']['subject'] = "[ERROR] Error deleting example file";
            $param['standard']['body'] = "Error deleting solution files by " . $user->uid . " at " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . " :
                solution id : " . $solution_id . "
                file id : " .  $solution_files_data->id . "
                file path : " . $solution_files_data->filepath."
            PDF path : " . $PdfStatus;
            $param['standard']['headers']=array('From'=>$from,'MIME-Version'=> '1.0',
                        'Content-Type'=> 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
                        'Content-Transfer-Encoding' => '8Bit',
                        'X-Mailer'=> 'Drupal','Cc' => $cc, 'Bcc' => $bcc);
    
            if (!drupal_mail('lab_migration', 'standard', $email_to, language_default(), $param, $from, TRUE))
            drupal_set_message('Error sending email message.', 'error');
        } 
        else {
          /* deleting example files database entries */     
          db_delete('lab_migration_solution_files')->condition('id', $solution_files_data->id)->execute();
        }
    }

    if (!$status)
    return FALSE;
    $query = db_select('lab_migration_solution');
    $query->fields('lab_migration_solution');
    $query->condition('id', $solution_id);
    $solution_q = $query->execute();
    $solution_data = $solution_q->fetchObject();
    if (!$solution_data)
      {
        drupal_set_message(t('Invalid solution.'), 'error');
        return FALSE;
      }
    $query = db_select('lab_migration_experiment');
    $query->fields('lab_migration_experiment');
    $query->condition('id', $solution_data->experiment_id);
    $experiment_q = $query->execute();
    $experiment_data = $experiment_q->fetchObject();
    $query = db_select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('id', $experiment_data->proposal_id);
    $proposal_q = $query->execute();
    $proposal_data = $proposal_q->fetchObject();
    $dir_path = $root_path . $proposal_data->directory_name . '/EXP' . $experiment_data->number . '/CODE' . $solution_data->code_number;
    if (is_dir($dir_path))
    {
        if (!rmdir($dir_path))
        {
            drupal_set_message(t('Error deleting folder !folder', array('!folder' => $dir_path)), 'error');

          /* sending email to admins */
            $email_to = variable_get('lab_migration_emails', '');
            $from = variable_get('lab_migration_from_email', '');
            $bcc="";
            $cc=variable_get('lab_migration_cc_emails', '');


            $param['standard']['subject'] = "[ERROR] Error deleting folder";
            $param['standard']['body'] = "Error deleting folder " . $dir_path . " by " . $user->uid . " at " . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $param['standard']['headers']=array('From'=>$from,'MIME-Version'=> '1.0',
                        'Content-Type'=> 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
                        'Content-Transfer-Encoding' => '8Bit',
                        'X-Mailer'=> 'Drupal','Cc' => $cc, 'Bcc' => $bcc);
        
            if (!drupal_mail('lab_migration', 'standard', $email_to, language_default(), $param, $from, TRUE))
                drupal_set_message('Error sending email message.', 'error');
          return FALSE;
        }
    } 
    else 
    {
        drupal_set_message(t('Cannot delete solution folder. !folder does not exists.', array('!folder' => $dir_path)), 'error');
        return FALSE;
    }

      /* deleting solution dependency and solution database entries */  
    db_delete('lab_migration_solution_dependency')->condition('solution_id', $solution_id)->execute();
    db_delete('lab_migration_solution')->condition('id', $solution_id)->execute();
    return $status;
}

function lab_migration_delete_experiment($experiment_id)
{
    $status = TRUE;
    $root_path = lab_migration_path();
    $query = db_select('lab_migration_experiment');
    $query->fields('lab_migration_experiment');
    $query->condition('id', $experiment_id);
    $experiment_q = $query->execute();
    $experiment_data = $experiment_q->fetchObject();
    $query = db_select('lab_migration_proposal');
    $query->fields('lab_migration_proposal');
    $query->condition('id', $experiment_data->proposal_id);
    $proposal_q = $query->execute();
    $proposal_data = $proposal_q->fetchObject();
    if (!$experiment_data)
    {
        drupal_set_message('Invalid experiment.', 'error');
        return FALSE;
    }
  /* deleting solutions */
    $query = db_select('lab_migration_solution');
    $query->fields('lab_migration_solution');
    $query->condition('experiment_id', $experiment_id);
    $solution_q = $query->execute();
    $delete_exp_folder = FALSE;
    while ($solution_data = $solution_q->fetchObject())
    {
        $delete_exp_folder = TRUE;
        if (!lab_migration_delete_solution($solution_data->id))
          $status = FALSE;
    }
    if (!$delete_exp_folder)
    {
        return TRUE;
    }
    if ($status)
    {
        $dir_path = $root_path . $proposal_data->directory_name . '/EXP' . $experiment_data->number;
        if (is_dir($dir_path))
        {
          $res = rmdir($dir_path);
          if (!$res)
          {
            drupal_set_message(t('Error deleting experiment folder !folder', array('!folder' => $dir_path)), 'error');
            /* sending email to admins */
            $email_to = variable_get('lab_migration_emails', '');
            $from = variable_get('lab_migration_from_email', '');
            $bcc="";
            $cc=variable_get('lab_migration_cc_emails', '');
            $param['standard']['subject'] = "[ERROR] Error deleting experiment folder";
            $param['standard']['body'] = "Error deleting folder " . $dir_path;
            $param['standard']['headers']=array('From'=>$from,'MIME-Version'=> '1.0',
                    'Content-Type'=> 'text/plain; charset=UTF-8; format=flowed; delsp=yes',
                    'Content-Transfer-Encoding' => '8Bit',
                    'X-Mailer'=> 'Drupal','Cc' => $cc, 'Bcc' => $bcc);
                if (!drupal_mail('lab_migration', 'standard', $email_to, language_default(), $param,$from, TRUE))
              drupal_set_message('Error sending email message.', 'error');
            return FALSE;
          } 
          else 
          {
            return TRUE;
          }
        } 
        else {
          drupal_set_message(t('Cannot delete experiment folder. !folder does not exists.', array('!folder' => $dir_path)), 'error');
          return FALSE;
        }
    }
  return FALSE;
}

function lab_migration_delete_lab($lab_id)
{
  $status = TRUE;
  $root_path = lab_migration_path();
  $query = db_select('lab_migration_proposal');
              $query->fields('lab_migration_proposal');
              $query->condition('id', $lab_id);
              $proposal_q = $query->execute();
  $proposal_data = $proposal_q->fetchObject();
  if (!$proposal_data)
  {
    drupal_set_message('Invalid Lab.', 'error');
    return FALSE;
  }
  /* delete experiments */
  $query = db_select('lab_migration_experiment');
              $query->fields('lab_migration_experiment');
              $query->condition('proposal_id', $proposal_data->id);
              $experiment_q = $query->execute();
  while ($experiment_data = $experiment_q->fetchObject())
  {
    if (!lab_migration_delete_experiment($experiment_data->id))
    {
      $status = FALSE;
    }
  }
  return $status;
}
