<?php
class EnDecryptText{
	function Encrypt_Text($cText) {
		$eText = $cText;
		
		// Get a random Number between 1 and 100. This will be the multiplier
		// for the Ascii value of the characters.
		$nEncKey = intval((100 * $this->Rnd()) + 1);
		
		// Loop until we get a random value betwee 5 and 7. This will be
		// the lenght (with leading zeros) of the value of the Characters.
		$nCharSize = 0;
		$nUpperBound = 10;
		$nLowerBound = 5;
		$nCharSize = intval(($nUpperBound - $nLowerBound + 1) * $this->Rnd() + $nLowerBound);
		
		// Encrypt the Size of the characters and convert it to String.
		// This size has to be standard so we always get the right character.
		$cCharSize = $this->fEncryptedKeySize($nCharSize);
		
		$cEncKey = $this->NumToString($nEncKey, $nCharSize); // Convert the KeyNumber to String with leading zeros.
		// Get the text to encrypt and it's size.
		$cEncryptedText = '';
		$nTextLenght = strlen($eText);
		// Loop thru the text one character at the time.
		for($nCounter = 1; $nCounter <= $nTextLenght; $nCounter++)
		{// Get the Next Character.
			$cChar = $this->Mid($eText, $nCounter, 1);
			// Get Ascii Value of the character multplied by the Key Number.
			$nChar = ord($cChar) * $nEncKey;
			// Get the String version of the Ascii Code with leading zeros.
			// using the Random generated Key Lenght.
			$cChar2 = $this->NumToString($nChar, $nCharSize);
			// Add the Newly generated character to the encrypted text variable.
			$cEncryptedText .= $cChar2;
		}
		// Separate the text in two to insert the enc
		// key in the middle of the string.
		$nLeft = intval(strlen($cEncryptedText) / 2);
		$cLeft = $this->strleft($cEncryptedText, $nLeft);
		$nRight = strlen($cEncryptedText) - $nLeft;
		$cRight = $this->strright($cEncryptedText, $nRight);
		// Add a Dummy string at the end to fool people.
		$cDummy = $this->CreateDummy();
		// Add all the strings together to get the final result.
		$this->InsertInTheMiddle($cEncryptedText, $cEncKey);
		$this->InsertInTheMiddle($cEncryptedText, $cCharSize);
		$cEncryptedText = $this->CreateDummy() . $cEncryptedText . $this->CreateDummy();
		return $cEncryptedText;
	}

	function Decrypt_Text($cText){
	  $cTempText = $cText;
	$cDecryptedText = '';
	$cText = '';
	// Replace alpha characters for zeros.
	for($nCounter = 1; $nCounter <= strlen($cTempText); $nCounter++)
	{$cChar = $this->Mid($cTempText, $nCounter, 1);
	if ($this->IsNumeric($cChar) == true)
	 $cText .= $cChar;
	else
	 $cText .= '0';
	}
	// Get the size of the key.
	$cText = $this->strleft($cText, strlen($cText) - 4);
	$cText = $this->strright($cText, strlen($cText) - 4);
	$nCharSize = 0;
	$this->Extract_Char_Size($cText, $nCharSize);
	$this->Extract_Enc_Key($cText, $nCharSize, $nEncKey);
	// Decrypt the Size of the encrypted characters.
	$nTextLenght = strlen($cText);
	// Loop thru text in increments of the Key Size.
	$nCounter = 1;
	do
	{// Get a Character the size of the key.
	$cChar = $this->Mid($cText, $nCounter, $nCharSize);
	// Get the value of the character.
	$nChar = $this->Val($cChar);
	// Divide the value by the Key to get the real value of the character.
	if ($nEncKey > 0) $nChar2 = $nChar / $nEncKey;
	// Convert the value to the character.
	$cChar2 = chr($nChar2);
	$cDecryptedText .= $cChar2;
	$nCounter += $nCharSize;
	}while ($nCounter <= strlen($cText));
	// Clear any unwanted spaces and show the decrypted text.
	return trim($cDecryptedText);
	}

	function Extract_Char_Size(&$cText, &$nCharSize)
	{// Get the half left side of the text.
	$nLeft = intval(strlen($cText) / 2);
	$cLeft = $this->strleft($cText, $nLeft);
	// Get the half right side of the text.
	$nRight = strlen($cText) - $nLeft;
	$cRight = $this->strright($cText, $nRight);
	// Get the key from the text.
	$nKeyEnc = $this->Val($this->strright($cLeft, 2));
	$nKeySize = $this->Val($this->strleft($cRight, 2));
	if ($nKeyEnc >= 5)
	$nCharSize = $nKeySize + $nKeyEnc;
	else
	$nCharSize = $nKeySize - $nKeyEnc;
	$cText = $this->strleft($cLeft, strlen($cLeft) - 2) . $this->strright($cRight, strlen($cRight) - 2);
	}

	function Extract_Enc_Key(&$cText, $nCharSize, &$nEncKey)
	{$cEncKey = '';
	// Get the real size of the text (without the previously
	// stored character size).
	$nLenght = strlen($cText) - $nCharSize;
	// Get the half left and half right sides of the text.
	$nLeft = intval($nLenght / 2);
	$cLeft = $this->strleft($cText, $nLeft);
	$nRight = $nLenght - $nLeft;
	$cRight = $this->strright($cText, $nRight);
	// Get the key from the text.
	$cEncKey = $this->Mid($cText, $nLeft + 1, $nCharSize);
	// Get the numeric value of the key.
	$nEncKey = $this->Val(trim($cEncKey));
	// Get the real text to decrypt (left side + right side).
	$cText = $cLeft . $cRight;
	}

	function fEncryptedKeySize($nKeySize)
	{$nLowerBound = 0;
	$nKeyEnc = intval(($nKeySize - $nLowerBound + 1) * $this->Rnd() + $nLowerBound);
	if ($nKeyEnc >= 5)
	$nKeySize = $nKeySize - $nKeyEnc;
	else
	$nKeySize = $nKeySize + $nKeyEnc;
	return $this->NumToString($nKeyEnc, 2) . $this->NumToString($nKeySize, 2);
	}

	function NumToString($nNumber, $nZeros)
	{// Check that the zeros to fill are not smaller than the actual size.
	$cNumber = trim(strval($nNumber));
	$nLenght = strlen($cNumber);
	if ($nZeros < $nLenght) $nZeros = 0;
	$nUpperBound = 122;
	$nLowerBound = 65;
	for($nCounter = 1; $nCounter <= ($nZeros - $nLenght); $nCounter++)
	{// Add a zero in front of the string until we reach the desired size.
	$lCreated = false;
	do
	{$nNumber = intval(($nUpperBound - $nLowerBound + 1) * $this->Rnd() + $nLowerBound);
	 if (($nNumber > 90) && ($nNumber < 97))
	  $lCreated = false;
	 else
	  $lCreated = true;
	}while ($lCreated == false);
	$cChar = chr($nNumber);
	$cNumber = $cChar . $cNumber;
	}
	// Return the resulting string.
	return $cNumber;
	}

	function InsertInTheMiddle(&$cSourceText, $cTextToInsert)
	{// Get the half left and half right sides of the text.
	$nLeft = intval(strlen($cSourceText) / 2);
	$cLeft = $this->strleft($cSourceText, $nLeft);
	$nRight = strlen($cSourceText) - $nLeft;
	$cRight = $this->strright($cSourceText, $nRight);
	// Insert cTextToString in the middle of cSourceText.
	$cSourceText = $cLeft . $cTextToInsert . $cRight;
	}

	//------------------------------------------------------------------------------------
	//
	//------------------------------------------------------------------------------------
	function CreateDummy()
	{$nUpperBound = 122;
	$nLowerBound = 48;
	$cDummy = '';
	for($nCounter = 1; $nCounter <= 4; $nCounter++)
	{$lCreated = false;
	do
	{$nDummy = intval(($nUpperBound - $nLowerBound + 1) * $this->Rnd() + $nLowerBound);
	 if ((($nDummy > 57) && ($nDummy < 65)) || (($nDummy > 90) && ($nDummy < 97)))
	  $lCreated = false;
	 else
	  $lCreated = true;
	}while ($lCreated == false);
	$cDummy .= chr($nDummy);
	}
	return $cDummy;
	}

	function strleft($tmp, $nLeft)
	{$len = strlen($tmp);
	if ($nLeft == 0)
	$str = '';
	else if ($nLeft < $len)
	$str = $this->Mid($tmp, 1, $nLeft);
	return $str;
	}

	function strright($tmp, $nRight)
	{$len = strlen($tmp);
	if ($nRight == 0)
	$str = '';
	else if ($nRight < $len)
	$str = $this->Mid($tmp, $len - $nRight + 1, $len);
	return $str;
	}

	function Mid($tmp, $start, $length)
	{$str = substr($tmp, $start - 1, $length);
	return $str;
	}

	function Rnd()
	{srand(); // Initialize random-number generator.
	do
	{$tmp = abs(tan(rand()));
	}while (($tmp > "1") || ($tmp < "0"));
	$tmp = $this->Mid($tmp, 1, 8);
	return $tmp;
	}

	function Val($tmp)
	{$length = strlen($tmp);
	$tmp2 = 0;
	for ($i = 1; $i <= $length; $i++)
	{$tmp1 = $this->Mid($tmp, $i, 1);
	if ($this->IsNumeric($tmp1) == 1)
	{$tmp2 .= $tmp1;}
	}
	return intval($tmp2);
	}
	
	function IsNumeric($cChar){
		$tmp = ord($cChar);
		if (($tmp < 48) || ($tmp > 57))
		$tmp = false;
		else
		$tmp = true;
		return $tmp;
	}
}
 ?>