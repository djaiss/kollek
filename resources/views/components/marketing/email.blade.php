{{-- A public email address written so that a scraper cannot read it. --}}

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
