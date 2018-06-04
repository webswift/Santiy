<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLicensesIndividualPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

		Schema::table('licenses', function ($table) {

			$table->smallInteger('volume');

			$table->decimal('priceUSD', 10, 2);
			$table->decimal('priceGBP', 10, 2);
			$table->decimal('priceEuro', 10, 2);

			$table->decimal('priceUSD_year', 10, 2);
			$table->decimal('priceGBP_year', 10, 2);
			$table->decimal('priceEuro_year', 10, 2);

			$table->string('discount', 10);

		});

		$licenses = App\Models\License::all();
		$licenceTypeDetail = App\Models\LicenseType::where('licenseClass', 'Multi')->where('type', 'Paid')->first();

		foreach ($licenses as $license){

				$license->priceUSD = $licenceTypeDetail->priceUSD;
				$license->priceGBP = $licenceTypeDetail->priceGBP;
				$license->priceEuro = $licenceTypeDetail->priceEuro;

				$license->priceUSD_year = $licenceTypeDetail->priceUSD_year;
				$license->priceGBP_year = $licenceTypeDetail->priceGBP_year;
				$license->priceEuro_year = $licenceTypeDetail->priceEuro_year;

				$license->discount = $licenceTypeDetail->discount;
				//$license->volume = $licenceTypeDetail->volume;

				$license->save();
		}

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::table('licenses', function ($table) {
			$table->dropColumn(['volume', 'priceUSD', 'priceGBP', 'priceEuro', 'priceUSD_year', 'priceGBP_year', 'priceEuro_year', 'discount']);
		});
    }
}
