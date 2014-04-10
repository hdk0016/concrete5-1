<?
namespace \Concrete\Controller\Dialog\User\Search;
use \Concrete\Controller\Backend\UI as BackendInterfaceController;
use User;
use UserAttributeKey;

class Customize extends BackendInterfaceController {

	protected $viewPath = '/system/dialogs/search/customize';
	protected $controllerActionPath = '/system/dialogs/user/search/customize';

	protected function canAccess() {
		$sh = Loader::helper('concrete/user');
		return $sh->canAccessUserSearchInterface();
	}

	public function view() {
		$selectedAKIDs = array();
		$fldc = \Concrete\Core\User\Search\ColumnSet\ColumnSet::getCurrent();
		$fldca = new \Concrete\Core\User\Search\ColumnSet\Available();
		$searchInstance = Loader::helper('text')->entities($_REQUEST['searchInstance']);
		$list = UserAttributeKey::getList();
		$this->set('list', $list);
		$this->set('form', Loader::helper('form'));
		$this->set('fldca', $fldca);
		$this->set('fldc', $fldc);
		$this->set('type', 'users');
	}

	public function submit() {
		if ($this->validateAction()) {
			$u = new User();
			$fdc = new \Concrete\Core\User\Search\ColumnSet\ColumnSet();
			$fldca = new \Concrete\Core\User\Search\ColumnSet\Available();
			foreach($_POST['column'] as $key) {
				$fdc->addColumn($fldca->getColumnByKey($key));
			}	
			$sortCol = $fldca->getColumnByKey($_POST['fSearchDefaultSort']);
			$fdc->setDefaultSortColumn($sortCol, $_POST['fSearchDefaultSortDirection']);
			$u->saveConfig('USER_LIST_DEFAULT_COLUMNS', serialize($fdc));

			$userList = new UserList();
			$columns = \Concrete\Core\User\Search\ColumnSet\ColumnSet::getCurrent();
			$col = $columns->getDefaultSortColumn();	
			$userList->sortBy($col->getColumnKey(), $col->getColumnDefaultSortDirection());

			$ilr = new \Concrete\Search\Result\Result($columns, $userList, URL::to('/system/search/users/submit'));
			$r = new \Concrete\Search\Response();
			$r->setMessage(t('User search columns saved successfully.'));
			$r->setSearchResult($ilr);
			$r->outputJSON();
		}
	}

}

