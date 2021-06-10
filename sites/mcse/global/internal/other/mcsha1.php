<?php
/**
 * Generates a Minecraft-style SHA1 hash.
 * @param string $str
 * @return string
 */
function mcsha1($str)
{
	$gmp = gmp_import(sha1($str, true));
	if(gmp_cmp($gmp, gmp_init("0x8000000000000000000000000000000000000000")) >= 0)
	{
		$gmp = gmp_mul(gmp_add(gmp_xor($gmp, gmp_init("0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF")), gmp_init(1)), gmp_init(-1));
	}
	return gmp_strval($gmp, 16);
}
