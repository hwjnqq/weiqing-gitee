<?php

/**
 * 贺卡模块微站定义
 *
 * @author 超级无聊
 * @url
 */
defined('IN_IA') or exit('Access Denied');

class Wl_hekaModuleSite extends WeModuleSite {

    public $tablename = 'heka_reply';

    public function getHomeTiles() {
        global $_W;
        $urls = array();
        $list = pdo_fetchall("SELECT name, id FROM " . tablename('rule') . " WHERE uniacid = '{$_W['uniacid']}' AND module = 'wl_heka'");
        if (!empty($list)) {
            foreach ($list as $row) {
                $urls[] = array('title' => $row['name'], 'url' => $this->createMobileUrl('index', array('id' => $row['id'])));
            }
        }
        return $urls;
    }

    public function doMobileindex() {
        global $_GPC, $_W;
        include $this->template('index');
    }

    public function doMobileshow() {
        global $_GPC, $_W;
        if (!empty($_GPC['cid'])) {
            $show = pdo_fetch("SELECT * FROM " . tablename('heka_list') . " WHERE id = :cid ORDER BY `id` DESC", array(':cid' => $_GPC['cid']));
        }
        if ($show == false) {
            $show = array(
                'id' => 0,
                'title' => '收卡人',
                'author' => '署名',
            );
        } else {
            pdo_update('heka_list', array('hits' => intval($show['hits']) + 1), array('id' => $show['id']));
        }
        $nikename = $_W['uniaccount']['name'];
        $card = $_GPC['card'];
        include $this->template($card);
    }

    public function doMobileset() {
        global $_GPC,$_W;
        $_GPC['title'] = urldecode($_GPC['title']);
        $_GPC['content'] = urldecode($_GPC['content']);
        $_GPC['author'] = urldecode($_GPC['author']);
        $_GPC['cardName'] = urldecode($_GPC['cardName']);
        $insert = array(
            'rid' => $_GPC['id'],
            'weid' => $_W['uniacid'],
            'title' => $_GPC['title'],
            'card' => $_GPC['card'],
            'content' => $_GPC['content'],
            'author' => $_GPC['author'],
            'cardName' => $_GPC['cardName'],
            'from_user' => $_W['fans']['from_user'],
            'create_time' => time(),
        );
        $temp = pdo_insert('heka_list', $insert);
        if ($temp == false) {
            $this->_message(0, '保存数据失败');
        } else {
            $id = pdo_insertid();
            $this->_message($id, '保存数据成功', 1, $_GPC['author']);
        }
    }

    public function doMobileshare() {
        global $_GPC;
        $id = $_GPC['cid'];
        $show = pdo_fetch("SELECT share FROM " . tablename('heka_list') . " WHERE id = :cid  ORDER BY `id` DESC", array(':cid' => $id));
        if ($show != false) {
            if (empty($show['share'])) {
                $show['share'] = 0;
            }
            pdo_update('heka_list', array('share' => intval($show['share']) + 1), array('id' => $id));
        }
    }

    public function _message($_id, $_msg, $_state = 0, $_username = '') {
        $_data = array(
            'id' => $_id,
            'msg' => $_msg,
            'state' => $_state,
        );
        if (!empty($_username)) {
            $_data['username'] = $_username;
        }
        echo json_encode($_data);
    }

    public function doWebList() {
        global $_GPC, $_W;
        $weid = $_W['uniacid'];
        if (checksubmit('delete')) {
            pdo_delete('heka_list', " id  IN  ('" . implode("','", $_GPC['select']) . "')");
            message('删除成功！', referer(),'success');
        }

        $where = ' WHERE `weid` = :weid';
        $params = array(':weid' => $_W['uniacid']);
        $sql = 'SELECT COUNT(*) FROM ' . tablename('heka_list') . $where;
        $total = pdo_fetchcolumn($sql, $params);

        if ($total > 0) {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 15;

            $sql = 'SELECT * FROM ' . tablename('heka_list') . $where . ' ORDER BY `create_time` DESC LIMIT ' .
                    ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($sql, $params);

            $pager = pagination($total, $pindex, $psize);
        }

        if (checksubmit('export')) {
            /* 输入到CSV文件 */
            $html = "\xEF\xBB\xBF";

            /* 输出表头 */
            $filter = array(
                'id' => '编号',
                'title' => '标题',
                'author' => '署名',
                'hits' => '点击数',
                'share' => '分享数',
                'create_time' => '创建时间',
            );


            foreach ($filter as $key => $value) {
                $html .= $value . "\t,";
            }
            $html .= "\n";

            foreach ($list as $key => $value) {
                foreach ($filter as $index => $title) {
                    if ($index != 'create_time') {
                        $html .= $value[$index] . "\t, ";
                    } else {
                        $html .= date('Y-m-d H:i:s', $value[$index]) . "\t, ";
                    }
                }
                $html .= "\n";
            }

            /* 输出CSV文件 */
            header("Content-type:text/csv");
            header("Content-Disposition:attachment; filename=全部数据.csv");
            echo $html;
            exit();
        }

        include $this->template('list');
    }

    public function doWebDeleteImage() {
        global $_GPC;
        load()->func('file');
        $id = intval($_GPC['id']);
        $sql = "SELECT id, picture FROM " . tablename($this->tablename) . " WHERE `id`=:id";
        $row = pdo_fetch($sql, array(':id' => $id));
        if (empty($row)) {
            message('抱歉，回复不存在或是已经被删除！', '', 'error');
        }
        if (pdo_update($this->tablename, array('picture' => ''), array('id' => $id))) {
            file_delete($row['picture']);
        }
        message('删除图片成功！', '', 'success');
    }

}
