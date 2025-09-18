<?php

namespace App\Exceptions;

use Exception;

class AccessDeniedException extends Exception
{
    protected $redirectUrl;
    protected $messageText;

    public function __construct($messageText, $redirectUrl)
    {
        parent::__construct($messageText);
        $this->redirectUrl = $redirectUrl;
        $this->messageText = $messageText;
    }

    public function render($request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status'   => 'error',
                'message'  => $this->messageText,
                'redirect' => $this->redirectUrl,
            ], 403);
        }
        return redirect($this->redirectUrl)->with('toast', [
            'type'    => 'error',
            'message' => $this->messageText,
        ]);
    }
}
