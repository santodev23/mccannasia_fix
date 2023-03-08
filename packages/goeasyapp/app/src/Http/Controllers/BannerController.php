<?php

namespace Goeasyapp\App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Goeasyapp\Core\Http\Hooks\BannerHook;
use Goeasyapp\Core\Http\Repositories\BannerRepository;
use App\Models\Language;

class BannerController extends Controller
{
    private $useHook;
    private $useRepository;
    private $configs;
    private $model;
    public function __construct(BannerHook $LoginValidateHook, BannerRepository $UserRepository)
    {
        $this->middleware('auth');
        $this->useHook = $LoginValidateHook;
        $this->useRepository = $UserRepository;

    }
    public function deleteItem($id)
    {
        $this->useRepository->deleteItem($id);
        return redirect()->intended('admin/campain')->with('success', 'Update item success!');
    }
    public function status($id)
    {
        $this->useRepository->changeStatus($id);
        return redirect()->back()->with('success', 'Update item success!');
    }
    public function list()
    {
        $lang = (session('locale') ? session('locale') : 'en');
        $language = Language::orderBy('updated_at', 'DESC')->where('code', $lang)->first();
        $language = ($language && $language->value != '') ? json_decode($language->value, true) : [];
        $td = [
            ['title' => __trans($language, 'All.id', 'ID'), 'value' => 'id'],
            ['title' => __trans($language, 'All.image', 'Image'), 'value' => 'image', 'type' => 'image'],
        ];
        $items = $this->useRepository->getPaginateWithRelation(['is_popup' => FALSE]);
        return view('app::' . $this->useRepository->getConfig()['aciton'] . '.list', [
            'title' => $this->useRepository->getConfig()['title'],
            'delete' => $this->useRepository->getConfig()['aciton'] . '.delete',
            'update' => $this->useRepository->getConfig()['aciton'] . '.update',
            'status' => $this->useRepository->getConfig()['aciton'] . '.status',
            'td' => $td,
            'items' => $items
        ]);

    }
    public function create()
    {
        $item = $this->useRepository->getModel();
        return view('app::' . $this->useRepository->getConfig()['aciton'] . '.create', [
            'item' => $item,
            'title' => $this->useRepository->getConfig()['title'] . ' Create',
            'route' => $this->useRepository->getConfig()['aciton'] . '.store'
        ]);
    }
    public function update($id)
    {
        $item = $this->useRepository->getModelById($id);
        $data = json($item->page_value);
        return view('app::' . $this->useRepository->getConfig()['aciton'] . '.edit', [
            'item' => $item,
            'data' => $data,
            'title' => $this->useRepository->getConfig()['title'] . ' Edit',
            'route' => $this->useRepository->getConfig()['aciton'] . '.store'
        ]);
    }
    public function store(Request $request)
    {
        $this->useRepository->updateModel($request);
        return redirect()->intended('admin/' . $this->useRepository->getConfig()['aciton'])
            ->with('success', 'Update item success!');
    }

	public function popupList() {

		$lang = (session('locale') ? session('locale') : 'en');
		$language = Language::orderBy('updated_at', 'DESC')->where('code', $lang)->first();
		$language = ($language && $language->value != '') ? json_decode($language->value, true) : [];
		$td = [
			['title' => __trans($language, 'All.id', 'ID'), 'value' => 'id'],
			['title' => __trans($language, 'All.image', 'Image'), 'value' => 'image', 'type' => 'image'],
		];
		$items = $this->useRepository->getPaginateWithRelation(['is_popup' => TRUE]);
		return view('app::' . $this->useRepository->getConfig()['aciton'] . '.list', [
			'title' => "Popup",
			'delete' => $this->useRepository->getConfig()['aciton'] . '.delete',
			'update' => $this->useRepository->getConfig()['aciton'] . '.update',
			'status' => $this->useRepository->getConfig()['aciton'] . '.status',
			'td' => $td,
			'items' => $items
		]);
	}
}