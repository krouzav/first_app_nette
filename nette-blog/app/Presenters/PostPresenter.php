<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Form as FormsForm;

class PostPresenter extends Nette\Application\UI\Presenter
{
    private Nette\Database\Explorer $database;

    public function __construct(Nette\Database\Explorer $database)
    {
        $this->database = $database;
    }

    public function renderShow(int $postId): void
    {
        $post = $this->database->table('posts')->get($postId);
        if (!$post) {
            $this->error('Post not found');
        }

        $this->template->post = $post;
        $this->template->comments = $post->related('comments')->order('created_at');
    }


    protected function createComponentCommentForm(): Form
    {

        $form = new Form;

        $form->addtext('name', 'Your name:')
            ->setRequired();

        $form->addEmail('email', 'Email:');

        $form->addTextArea('content', 'Comment:')
            ->setRequired();

        $form->addSubmit('send', 'Publish comment');

        $form->onSuccess[] = [$this, 'commentFormSucceeded'];

        return $form;
    }

    public function commentFormSucceeded(\stdClass $values): void
    {
        $postId = $this->getParameter('postId');

        $this->database->table('comments')->insert([
            'post_id' => $postId,
            'name' => $values->name,
            'email' => $values->email,
            'content' => $values->content,
        ]);

        $this->flashMessage('Thank you for your comment', 'success');
        $this->redirect('this');
    }
}