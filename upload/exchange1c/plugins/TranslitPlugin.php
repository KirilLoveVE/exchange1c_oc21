<?php

use Exchange1C\Plugin\Plugin;

/**
 * Демонстрационный пример плагина. Производит транслитерацию имени
 * категории или товара при их создании и записывает это значение в
 * поле keyword (SEO URL).
 */

class TranslitPlugin extends Plugin {

	/**
	 * Таблица транслитерации.
	 *
	 * @var array
	 */
	private $table = array();

	/**
	 * Идентификатор языка по умолчанию.
	 *
	 * @var int
	 */
	private $languageId;

	/**
	 * Инициализация плагина.
	 *
	 * @return void
	 */
	public function init()
	{
		$this->languageId = $this->config->get('config_language_id');
		$this->table = $this->getTable();

		$this->addEventListener('beforeAddCategory', 'translitCategory');
		$this->addEventListener('beforeAddProduct', 'translitProduct');
	}

	/**
	 * Транслитерация имени категории.
	 *
	 * @event beforeAddCategory
	 * @param string $category1cId
	 * @param array &$categoryData
	 * @return void
	 */
	public function translitCategory($category1cId, &$categoryData)
	{
		if (isset($categoryData['category_description'][$this->languageId]))
		{
			$categoryName = $categoryData['category_description'][$this->languageId]['name'];
			
			$keyword = $this->clearString(
				$this->translit($categoryName)
			);

			if ( ! $this->hasDuplicate($keyword))
			{
				$categoryData['keyword'] = $keyword;
			}
		}
	}

	/**
	 * Транслитерация имени товара.
	 *
	 * @event beforeAddProduct
	 * @param string $product1cId
	 * @param array &$productData
	 * @return void
	 */
	public function translitProduct($product1cId, &$productData)
	{
		if (isset($productData['product_description'][$this->languageId]))
		{
			$productName = $productData['product_description'][$this->languageId]['name'];

			$keyword = $this->clearString(
				$this->translit($productName)
			);
			
			if ( ! $this->hasDuplicate($keyword))
			{
				$productData['keyword'] = $keyword;
			}
		}
	}

	/**
	 * Транслитерация строки.
	 *
	 * @param string $string
	 * @return string
	 */
	private function translit($string)
	{
		$output = $string;

		if (is_array($this->table) && ! empty($this->table))
		{
			$output = strtr($string, $this->table);
		}

		return $output;
	}

	/**
	 * Проверка дубликатов URL.
	 *
	 * @param string $keyword
	 * @return bool
	 */
	private function hasDuplicate($keyword)
	{
		$dbPrefix = DB_PREFIX;

		$query = $this->db->query("SELECT * FROM {$dbPrefix}url_alias WHERE keyword = '{$keyword}'");

		if ( ! empty($query->row)) return true;

		return false;
	}

	/**
	 * Получение таблицы транслитерации.
	 *
	 * @return array
	 */
	private function getTable()
	{
		return array (
			'&'=>'and','%'=>'','\''=>'','À'=>'A','À'=>'A','Á'=>'A','Á'=>'A','Â'=>'A','Â'=>'A','Ã'=>'A',
			'Ã'=>'A','Ä'=>'e','Ä'=>'A','Å'=>'A','Å'=>'A','Æ'=>'e','Æ'=>'E','Ā'=>'A','Ą'=>'A','Ă'=>'A',
			'Ç'=>'C','Ç'=>'C','Ć'=>'C','Č'=>'C','Ĉ'=>'C','Ċ'=>'C','Ď'=>'D','Đ'=>'D','È'=>'E','È'=>'E',
			'É'=>'E','É'=>'E','Ê'=>'E','Ê'=>'E','Ë'=>'E','Ë'=>'E','Ē'=>'E','Ę'=>'E','Ě'=>'E','Ĕ'=>'E',
			'Ė'=>'E','Ĝ'=>'G','Ğ'=>'G','Ġ'=>'G','Ģ'=>'G','Ĥ'=>'H','Ħ'=>'H','Ì'=>'I','Ì'=>'I','Í'=>'I',
			'Í'=>'I','Î'=>'I','Î'=>'I','Ï'=>'I','Ï'=>'I','Ī'=>'I','Ĩ'=>'I','Ĭ'=>'I','Į'=>'I','İ'=>'I',
			'Ĳ'=>'J','Ĵ'=>'J','Ķ'=>'K','Ľ'=>'K','Ĺ'=>'K','Ļ'=>'K','Ŀ'=>'K','Ñ'=>'N','Ñ'=>'N','Ń'=>'N',
			'Ň'=>'N','Ņ'=>'N','Ŋ'=>'N','Ò'=>'O','Ò'=>'O','Ó'=>'O','Ó'=>'O','Ô'=>'O','Ô'=>'O','Õ'=>'O',
			'Õ'=>'O','Ö'=>'e','Ö'=>'e','Ø'=>'O','Ø'=>'O','Ō'=>'O','Ő'=>'O','Ŏ'=>'O','Œ'=>'E','Ŕ'=>'R',
			'Ř'=>'R','Ŗ'=>'R','Ś'=>'S','Ş'=>'S','Ŝ'=>'S','Ș'=>'S','Ť'=>'T','Ţ'=>'T','Ŧ'=>'T','Ț'=>'T',
			'Ù'=>'U','Ù'=>'U','Ú'=>'U','Ú'=>'U','Û'=>'U','Û'=>'U','Ü'=>'e','Ū'=>'U','Ü'=>'e','Ů'=>'U',
			'Ű'=>'U','Ŭ'=>'U','Ũ'=>'U','Ų'=>'U','Ŵ'=>'W','Ŷ'=>'Y','Ÿ'=>'Y','Ź'=>'Z','Ż'=>'Z','à'=>'a',
			'á'=>'a','â'=>'a','ã'=>'a','ä'=>'e','ä'=>'e','å'=>'a','ā'=>'a','ą'=>'a','ă'=>'a','å'=>'a',
			'æ'=>'e','ç'=>'c','ć'=>'c','č'=>'c','ĉ'=>'c','ċ'=>'c','ď'=>'d','đ'=>'d','è'=>'e','é'=>'e',
			'ê'=>'e','ë'=>'e','ē'=>'e','ę'=>'e','ě'=>'e','ĕ'=>'e','ė'=>'e','ƒ'=>'f','ĝ'=>'g','ğ'=>'g',
			'ġ'=>'g','ģ'=>'g','ĥ'=>'h','ħ'=>'h','ì'=>'i','í'=>'i','î'=>'i','ï'=>'i','ī'=>'i','ĩ'=>'i',
			'ĭ'=>'i','į'=>'i','ı'=>'i','ĳ'=>'j','ĵ'=>'j','ķ'=>'k','ĸ'=>'k','ł'=>'l','ľ'=>'l','ĺ'=>'l',
			'ļ'=>'l','ŀ'=>'l','ñ'=>'n','ń'=>'n','ň'=>'n','ņ'=>'n','ŉ'=>'n','ŋ'=>'n','ò'=>'o','ó'=>'o',
			'ô'=>'o','õ'=>'o','ö'=>'e','ö'=>'e','ø'=>'o','ō'=>'o','ő'=>'o','ŏ'=>'o','œ'=>'e','ŕ'=>'r',
			'ř'=>'r','ŗ'=>'r','ù'=>'u','ú'=>'u','û'=>'u','ü'=>'e','ū'=>'u','ü'=>'e','ů'=>'u','ű'=>'u',
			'ŭ'=>'u','ũ'=>'u','ų'=>'u','ŵ'=>'w','ÿ'=>'y','ŷ'=>'y','ż'=>'z','ź'=>'z','ß'=>'s','ſ'=>'s',
			'Α'=>'A','Ά'=>'A','Β'=>'B','Γ'=>'G','Δ'=>'D','Ε'=>'E','Έ'=>'E','Ζ'=>'Z','Η'=>'I','Ή'=>'I',
			'Θ'=>'TH','Ι'=>'I','Ί'=>'I','Ϊ'=>'I','Κ'=>'K','Λ'=>'L','Μ'=>'M','Ν'=>'N','Ξ'=>'KS','Ο'=>'O',
			'Ό'=>'O','Π'=>'P','Ρ'=>'R','Σ'=>'S','Τ'=>'T','Υ'=>'Y','Ύ'=>'Y','Ϋ'=>'Y','Φ'=>'F','Χ'=>'X',
			'Ψ'=>'PS','Ω'=>'O','Ώ'=>'O','α'=>'a','ά'=>'a','β'=>'b','γ'=>'g','δ'=>'d','ε'=>'e','έ'=>'e',
			'ζ'=>'z','η'=>'i','ή'=>'i','θ'=>'th','ι'=>'i','ί'=>'i','ϊ'=>'i','ΐ'=>'i','κ'=>'k','λ'=>'l',
			'μ'=>'m','ν'=>'n','ξ'=>'ks','ο'=>'o','ό'=>'o','π'=>'p','ρ'=>'r','σ'=>'s','τ'=>'t','υ'=>'y',
			'ύ'=>'y','ϋ'=>'y','ΰ'=>'y','φ'=>'f','χ'=>'x','ψ'=>'ps','ω'=>'o','ώ'=>'o','А'=>'a','Б'=>'b',
			'В'=>'v','Г'=>'g','Д'=>'d','Е'=>'e','Ё'=>'yo','Ж'=>'zh','З'=>'z','И'=>'i','Й'=>'j','К'=>'k',
			'Л'=>'l','М'=>'m','Н'=>'n','О'=>'o','П'=>'p','Р'=>'r','С'=>'s','Т'=>'t','У'=>'u','Ф'=>'f',
			'Х'=>'x','Ц'=>'cz','Ч'=>'ch','Ш'=>'sh','Щ'=>'shh','Ъ'=>'','Ы'=>'yi','Ь'=>'','Э'=>'e','Ю'=>'yu',
			'Я'=>'ya','а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo','ж'=>'zh','з'=>'z',
			'и'=>'i','й'=>'j','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s',
			'т'=>'t','у'=>'u','ф'=>'f','х'=>'x','ц'=>'cz','ч'=>'ch','ш'=>'sh','щ'=>'shh','ъ'=>'','ы'=>'yi',
			'ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya'
		);
	}

	/**
	 * Очистка строки от ненужных символов.
	 *
	 * @param string $string
	 * @return string
	 */
	private function clearString($string)
	{
		$string = mb_strtolower($string, 'utf-8');

		$string = htmlspecialchars_decode($string);
		
		$string = preg_replace('/[^a-z_ 0-9]/', '', $string);
		
		$string = str_replace(' ', '-', $string);

		return $string;
	}

}
