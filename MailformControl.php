<?php

use \Nette\Application\UI\Control,
    \Nette\Application\UI\Form,
    \Nette\Mail\Message;


/**
 * MailformControl
 *  
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 */ 
class MailformControl extends Control
{
    /** @var string  path to the template file */
    protected $templatePath;
    
    /** @var string */
    protected $bodyHeader;
    
    /** @var string */
    protected $bodyFooter;
    
    /** @var Message */
    protected $message;
    
    /** @var string */
    protected $flashSuccess;
    
    /** @var Form */
    protected $form;
    

    
    public function __construct()
    {
        parent::__construct();
        
        $this->templatePath = '';
        
        $this->form = new Form();
        
        $this->bodyHeader = '';
        $this->bodyFooter = '';
        
        $this->message = new Message();
        
        $this->flashSuccess = '';
    }
    
    
    
    /**
     * @return void
     */         
    public function render()
    {   
        $this->amendValues();
        
        $this->template->setFile($this->templatePath);
        
        $this->template->render(); 
    }
    
    
    
    /**
     * @return Form
     */
    protected function createComponentForm()
    {
        if (count($this->form->controls) == 0) {
            $this->setForm( $this->createDefaultForm() );
        }
        
        return $this->form;
    }         
    
    
    
    /**
     * @param Form
     * @return void     
     */
    public function handleFormSubmitted($form)
    {
        $this->amendValues();
        
        // Create mail body
        $values = $form->getValues();
        
        $mailBody = $this->bodyHeader;        
        $mailBody .= '<table>';

        foreach ($form->controls as $control) {
            if ($control->label) {
                $mailBody .= '
                    <tr>
                      <th>' . $control->label . '</th>
                      <td>' . nl2br($control->value) . '</td>
                    </tr>
                ';
            }
        }
        $mailBody .= '</table>';        
        $mailBody .= $this->bodyFooter;
        
        // Send e-mail
        $this->message->htmlBody = $mailBody;
        $this->message->send();
        
        // Redirect
        $this->flashMessage($this->flashSuccess);
        $this->redirect('this');
    }
    
    
    
    /********************* Control setup *********************/    
    
    
    
    /**
     * @param string
     * @return MailformControl     
     */         
    public function setTemplate($template)
    {
        $this->templatePath = $template;
        return $this;
    }
    
    
    
    /**
     * @param string
     * @param string     
     * @return MailformControl     
     */         
    public function setFrom($email, $name = NULL)
    {
        $this->message->setFrom($email, $name);
        return $this;
    }
    
    
    
    /**
     * @param string
     * @return MailformControl
     */         
    public function addTo($email, $name = NULL)
    {
        $this->message->addTo($email, $name);
        return $this;
    }
    
    
    
    /**
     * @param string
     * @return MailformControl     
     */         
    public function setSubject($subject)
    {
        $this->message->setSubject($subject);
        return $this;
    }
    
    
    
    /**
     * @param string
     * @return MailformControl     
     */
    public function setBodyHeader($bodyHeader)
    {
        $this->bodyHeader = $bodyHeader;
        return $this;
    }
    
    
    
    /**
     * @param string
     * @return MailformControl     
     */
    public function setBodyFooter($bodyFooter)
    {
        $this->bodyFooter = $bodyFooter;
        return $this;
    }  
    
    
    
    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }
    
    
    
    /**
     * @param Form
     * @return MailformControl
     */
    public function setForm(Form $form)
    {
        $this->form = $form;
        
        $this->form->onSuccess[] = callback($this, 'handleFormSubmitted');
                
        return $this;
    }         
    
    
    
    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }  
    
    
    
    /********************* Class methods *********************/
    
    
    
    /**
     * @return void
     */         
    protected function amendValues()
    {
        // TemplatePath
        if ($this->templatePath == '') $this->templatePath = __DIR__ . '/MailformControl.latte';
        
        // Message
        if ($this->message->from == '') {
            $this->message->from = 'info@' . $_SERVER['HTTP_HOST'];
        }
        if (count($this->message->getHeader('to')) == 0) {
            $this->message->addTo('info@' . $_SERVER['HTTP_HOST']);
        }
        if ($this->message->subject == '') {
            $this->message->subject = 'Byl vyplněn e-mailový formulář.';
        }
        
        // FlashSuccess
        if ($this->flashSuccess == '') $this->flashSuccess = 'Zpráva byla úspěšně odeslána.';
    }
    
    
    
    /**
     * @return Form
     */
    protected function createDefaultForm()
    {
        $form = new Form();
        $form->addText('name', 'Jméno: ')
            ->addRule(Form::FILLED, 'Vyplňte prosím jméno.');
        $form->addText('email', 'E-mail: ')
            ->addRule(Form::EMAIL, 'Vyplňte prosím e-mail.');
        $form->addTextarea('message', 'Zpráva: ')
            ->addRule(Form::FILLED, 'Vyplňte prosím obsah zprávy.');
        $form->addSubmit('send', 'Odeslat');
        
        return $form;
    }         

}