<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\common\library\Auth;
use think\Db;

/**
 * 会员管理
 *
 * @icon fa fa-user
 */
class User extends Backend {

    protected $relationSearch = true;
    protected $searchFields = 'id,username,nickname';

    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize() {
        parent::_initialize();
        $this->model = model('User');
    }


    public function money($ids = null) {

        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $result = false;
                Db::startTrans();
                try {
                    if ($params['type'] == 'plus') {
                        $result = db::name('user')->where(['id' => $ids])->setInc('money', $params['money']);
                    }
                    if ($params['type'] == 'reduce') {
                        $result = db::name('user')->where(['id' => $ids])->setDec('money', $params['money']);
                    }
                    if ($params['type'] == 'reset') {
                        $result = db::name('user')->where(['id' => $ids])->update(['money' => $params['money']]);
                    }
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }

        return $this->view->fetch();
    }

    /**
     * 查看
     */
    public function index() {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = $this->model->with(['group', 'agency'])->where($where)->order($sort, $order)->paginate($limit);
            foreach ($list as $k => $v) {
                $v->avatar = $v->avatar ? cdnurl($v->avatar, true) : letter_avatar($v->nickname);
                $v->hidden(['password', 'salt']);
            }
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add() {
        if ($this->request->isPost()) {
            $this->token();
        }
        return parent::add();
    }

    /**
     * 编辑
     */
    public function edit($ids = null) {
        if ($this->request->isPost()) {
            $this->token();
        }
        $row = $this->model->get($ids);
        $this->modelValidate = true;
        if (!$row) {
            $this->error(__('No Results were found'));
        }
//        $this->view->assign('groupList', build_select('row[group_id]', \app\admin\model\UserGroup::column('id,name'), $row['group_id'], ['class' => 'form-control selectpicker']));
		$this->assign([
			'agency' => Db::name('user_agency')->order(['weigh' => 'desc'])->select()
		]);
        return parent::edit($ids);
    }

    /**
     * 删除
     */
    public function del($ids = "") {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ? $ids : $this->request->post("ids");
        $row = $this->model->get($ids);
        $this->modelValidate = true;
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        Auth::instance()->delete($row['id']);
        $this->success();
    }

}
