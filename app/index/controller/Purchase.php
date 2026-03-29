<?php
declare(strict_types=1);

namespace app\index\controller;

use app\admin\model\TenantPackageModel;
use think\facade\Db;
use think\facade\View;
use think\facade\Request;

/**
 * 租户自助购买页面
 */
class Purchase
{
	/**
	 * 套餐选择页面
	 */
	public function index()
	{
		$packages = TenantPackageModel::order('sort')
			->order('id')
			->select()
			->toArray();

		foreach ($packages as &$pkg) {
			$expireDays = $pkg['expire_days'] ?? null;
			if ($expireDays && $expireDays > 0) {
				$pkg['expire_days_text'] = $expireDays . '天';
			} else {
				$pkg['expire_days_text'] = '永久';
			}

			$pkg['price_text'] = number_format($pkg['price'] ?? 0, 2);

			// 获取套餐功能列表
			$features = Db::name('tenant_package_feature')
				->where('package_id', $pkg['id'])
				->where('is_enabled', 1)
				->column('feature_name');
			$pkg['features'] = $features;
		}

		View::assign('packages', $packages);
		return View::fetch('purchase/index');
	}

	/**
	 * 租户注册+购买页面
	 */
	public function register()
	{
		$packages = TenantPackageModel::order('sort')
			->order('id')
			->select()
			->toArray();

		foreach ($packages as &$pkg) {
			$expireDays = $pkg['expire_days'] ?? null;
			if ($expireDays && $expireDays > 0) {
				$pkg['expire_days_text'] = $expireDays . '天';
			} else {
				$pkg['expire_days_text'] = '永久';
			}
			$pkg['price_text'] = number_format($pkg['price'] ?? 0, 2);
		}

		View::assign('packages', $packages);
		return View::fetch('purchase/register');
	}

	/**
	 * 购买表单页面
	 */
	public function form()
	{
		$packageId = (int) Request::get('package_id', 0);
		$package = TenantPackageModel::find($packageId);

		if (!$package) {
			return View::fetch('purchase/package_not_found');
		}

		$pkgData = $package->toArray();
		$expireDays = $pkgData['expire_days'] ?? null;
		if ($expireDays && $expireDays > 0) {
			$pkgData['expire_days_text'] = $expireDays . '天';
		} else {
			$pkgData['expire_days_text'] = '永久';
		}
		$pkgData['price_text'] = number_format($pkgData['price'] ?? 0, 2);

		View::assign('package', $pkgData);
		return View::fetch('purchase/form');
	}
}
