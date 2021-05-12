<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use App\Mail\Exception as ExceptionMail;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception){
        if ($this->shouldReport($exception)) {
            $this->sendExceptionEmail($exception);
        }
        parent::report($exception);
    }

    public function sendExceptionEmail(Throwable $exception)
    {
        try {
            $mail = 'APP URL - ' . env('APP_URL') . ' <br>';
            $mail .= 'LOCATION - ' . request()->url() . ' <br>';

            $mail .= 'EXCEPTION - ' . $exception->getMessage();
            if (env('SENDCRASHREPORTTODEV')) {
                Mail::queue(new ExceptionMail($mail));
            }
        } catch (Exception $e) {
            //
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception){
        if (!$this->shouldReport($exception)) {
            return parent::render($request, $exception);
        }
        if(config('app.debug')) {
            return parent::render($request, $exception);
        }
        return parent::render($request, $exception);
    }
}
