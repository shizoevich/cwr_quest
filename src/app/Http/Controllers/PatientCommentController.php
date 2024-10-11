<?php

namespace App\Http\Controllers;

use App\Jobs\Comments\ParseCommentMentions;
use App\PatientComment;
use App\Http\Requests\Patient\Comment\StoreRequest;
use App\User;

class PatientCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return PatientComment::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRequest $request)
    {
        $responseStatusCode = 201;
        $data = $request->validated();

        if (isset($data['comment'])) {
            $data['comment'] = preg_replace("/<div><br><\/div>/", " ", $data['comment']);
            $data['comment'] = preg_replace("/<div>/", "<br>", $data['comment']);
            $data['comment'] = strip_tags($data['comment'], '<span><br>');
            $data['comment'] = preg_replace("/<br>$/", "", $data['comment']);
            $data['comment'] = trim($data['comment']);
        }

        if (isset($data['metadata'])) {
            $data['metadata'] = array_only($data['metadata'], ['old_time', 'new_time', 'document_to_fill_name', 'visit_reason']);
        }

        $user = \Auth::user();
        if (empty($data['provider_id'])) {
            if ($user->isAdmin()) {
                $data['admin_id'] = $user->id;
            }
            if ($user->isProvider()) {
                $providerId = User::find($user->id)->provider_id;
                $data['provider_id'] = $providerId;
            }
        }

        $comment = PatientComment::create($data);
        if (isset($comment) && isset($data['comment'])) {
            \Bus::dispatchNow(new ParseCommentMentions($data['comment'], $comment->id, 'PatientComment', $comment->patient->id));
        }

        return response()->json([
            'success' => true,
            'sanitized-message' => $comment->comment,
        ], $responseStatusCode);
    }

    /**
     * @param $comment
     *
     * @return mixed
     */
    public function show($comment)
    {
        return $comment;
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param PatientComment $comment
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function destroy(PatientComment $comment)
    {
        $comment->delete();

        return response([], 204);
    }
}
