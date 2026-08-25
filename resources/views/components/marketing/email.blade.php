{{--
  A public email address that a scraper cannot read. The address never reaches the html:
  every character is exclusive ored with a key drawn per render and written as hexadecimal
  inside the script below, which fills the empty link in while the parser is still on the
  page, so a reader never sees anything else.

  The address only exists once javascript has run: with it switched off the link stays
  empty. That is the price of not publishing the address, and it is paid on one link.

  The scheme is ported from mokhosh/muddle (MIT), which has no Laravel 13 release.
--}}

@props (['address'])

@php
    $key = random_int(0, 255);
    $id = 'e'.random_int(100000, 999999);

    $codes = implode(' ', array_map(
        fn (string $character): string => dechex(ord($character) ^ $key),
        str_split($address),
    ));
@endphp

<a id="{{ $id }}" {{ $attributes }}></a>
<script>
  (() => {
    const address = @js ($codes)
      .split(' ')
      .map((code) => String.fromCharCode(parseInt(code, 16) ^ {{ $key }}))
      .join('');
    const link = document.getElementById(@js ($id));

    link.href = 'mailto:' + address;
    link.textContent = address;
  })();
</script>
