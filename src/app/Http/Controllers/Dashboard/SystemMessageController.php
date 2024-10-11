<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\SystemMessages\EditSystemMessage;
use App\Http\Requests\SystemMessages\StoreSystemMessage;
use App\SystemMessage;
use App\UserReadedSystemMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SystemMessageController extends Controller
{
    /**
     * SystemMessageController constructor.
     */
    public function __construct() {

    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index() {
        $messages = SystemMessage::orderBy('created_at', 'desc')->paginate(5);

        return view('dashboard.system-messages.index', compact('messages'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showAdd() {
        return view('dashboard.system-messages.add');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function add(StoreSystemMessage $request) {
        SystemMessage::create([
            'user_id' => Auth::id(),
            'title' => $request->input('title'),
            'text' => $request->input('text'),
            'modal_class' => $request->input('modal_class'),
            'only_for_admin' => !is_null($request->input('only_for_admin')),
        ]);

        return redirect()->route('system-messages');
    }

    /**
     * @param $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showEdit($id) {
        $message = SystemMessage::findOrFail($id);

        return view('dashboard.system-messages.add', compact('message'));
    }

    public function edit(EditSystemMessage $request) {
        SystemMessage::where('id', $request->input('id'))->update([
            'text' => $request->input('text'),
            'title' => $request->input('title'),
            'user_id' => Auth::id(),
            'modal_class' => $request->input('modal_class'),
        ]);

        return redirect()->route('system-messages');
    }

    /**
     * @param $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id) {
        SystemMessage::where('id', $id)->delete();

        return redirect()->route('system-messages');
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Foundation\Application|\Symfony\Component\HttpFoundation\Response
     */
    public function get(Request $request) {
        $userId = Auth::id();
        $messages = SystemMessage::selectRaw("
                system_messages.*, (
                    	SELECT COUNT(*) 
                        FROM users_readed_system_messages
                        WHERE user_id=$userId AND system_message_id = system_messages.id
                ) AS is_readed
            ")
            ->when($request->input('page-name'), function($query, $pageName) {
                $query->where('page', '=', $pageName);
            })
            ->where(function($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', Carbon::now()->toDateTimeString());
            })
            ->having('is_readed', '=',0)
            ->orderBy('created_at');
        if(!Auth::user()->isAdmin()) {
            $messages = $messages->where('only_for_admin', false);
        }
        $messages = $messages->get();

        return response($messages);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function setReaded(Request $request) {
        $this->validate($request, [
            'messageId' => 'required|numeric|exists:system_messages,id',
        ]);

        $userId = Auth::id();
        UserReadedSystemMessage::create([
            'user_id' => $userId,
            'system_message_id' => $request->messageId,
        ]);

        return response([]);
    }
}
