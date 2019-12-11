<?php

class Messages extends Controller
{
    public function __construct() {
        !isUserLogged() ? redirect('users.login') : $this->model('Message');
    }

    public function index() {
        $this->view('messages.index');
    }

    public function message($id) {
        
        // Get message with its replies
        $data = $this->model->getMessageWithReplies($id);

        if (!empty($data) && $data[0]->message_parent_id == 0) {

            $message = $data[0];
            $message->replies = [];

            foreach($data as $item) {
                if ($item->message_parent_id == $message->id) {
                    $message->replies[] = $item;
                }
            }
    
            foreach($message->replies as $k => $reply) {
                $this->prepareMessage($data, $reply);
            }
    
            $this->view('messages.message', ['message' => $message]);
            
        } else {
            $this->view('404');
        }

    }

    public function prepareMessage($data, $reply) {
  
        $reply->replies = [];

        foreach($data as $k => $val) {
            if ($val->message_parent_id == $reply->id) {
                $reply->replies[] = $val;

                foreach($reply->replies as $i => $v) {
                    $this->prepareMessage($data, $v);
                }
            }
        }
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST'):

            // Get data
            $data = $_POST;
            $data = sanitize($data);

            // Check if message is empty
            if (empty($data['message'])):
                $data['message_err'] = 'Plaese write a message.';
            elseif (strlen($data['message']) <= 30):
                $data['message_err'] = 'message must be more than 30 characters.';
            else:
                $data['message_err'] = '';
            endif;

            // Store Or Back with errors
            if (empty($data['message_err'])):
                $data['user_id'] = $_SESSION['user_id'];
                $this->model->addMessage($data);
                flash('msg', 'Message is Added Successfully!');
                redirect('messages');
            else:
                $this->view('messages.index', ['data' => $data]);
            endif;
        else:
            redirect('users.login');
        endif;
    }


}