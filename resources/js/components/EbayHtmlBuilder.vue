<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
    product: Object,
})

const logoSvg = `
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 974 775" style="width:220px;max-width:100%;height:auto;">
    <g xmlns="http://www.w3.org/2000/svg" id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
        <g id="Group-2" transform="translate(0.8613, 0.2988)">
            <g id="WORKS" transform="translate(258.8887, 716.4199)" fill="#12B981" fill-rule="nonzero">
                <path d="M15.1953125,58.28125 L0,1.015625 L7.7734375,1.015625 L16.484375,38.5546875 C17.421875,42.4869792 18.2291667,46.3932292 18.90625,50.2734375 C20.3645833,44.1536458 21.2239583,40.625 21.484375,39.6875 L32.3828125,1.015625 L41.5234375,1.015625 L49.7265625,30 C51.7838542,37.1875 53.2682292,43.9453125 54.1796875,50.2734375 C54.9088542,46.6536458 55.859375,42.5 57.03125,37.8125 L66.015625,1.015625 L73.6328125,1.015625 L57.9296875,58.28125 L50.625,58.28125 L38.5546875,14.6484375 C37.5390625,11.0026042 36.9401042,8.76302083 36.7578125,7.9296875 C36.1588542,10.5598958 35.5989583,12.7994792 35.078125,14.6484375 L22.9296875,58.28125 L15.1953125,58.28125 Z" id="Path"/>
                <path d="M118.398438,30.390625 C118.398438,20.8854167 120.950521,13.4440104 126.054688,8.06640625 C131.158854,2.68880208 137.747396,0 145.820312,0 C151.106771,0 155.872396,1.26302083 160.117188,3.7890625 C164.361979,6.31510417 167.597656,9.83723958 169.824219,14.3554688 C172.050781,18.8736979 173.164062,23.9973958 173.164062,29.7265625 C173.164062,35.5338542 171.992188,40.7291667 169.648438,45.3125 C167.304688,49.8958333 163.984375,53.3658854 159.6875,55.7226562 C155.390625,58.0794271 150.755208,59.2578125 145.78125,59.2578125 C140.390625,59.2578125 135.572917,57.9557292 131.328125,55.3515625 C127.083333,52.7473958 123.867188,49.1927083 121.679688,44.6875 C119.492188,40.1822917 118.398438,35.4166667 118.398438,30.390625 Z M126.210938,30.5078125 C126.210938,37.4088542 128.066406,42.8450521 131.777344,46.8164062 C135.488281,50.7877604 140.143229,52.7734375 145.742188,52.7734375 C151.445312,52.7734375 156.139323,50.7682292 159.824219,46.7578125 C163.509115,42.7473958 165.351562,37.0572917 165.351562,29.6875 C165.351562,25.0260417 164.563802,20.9570312 162.988281,17.4804688 C161.41276,14.0039062 159.108073,11.3085938 156.074219,9.39453125 C153.040365,7.48046875 149.635417,6.5234375 145.859375,6.5234375 C140.494792,6.5234375 135.878906,8.36588542 132.011719,12.0507812 C128.144531,15.7356771 126.210938,21.8880208 126.210938,30.5078125 Z" id="Shape"/>
                <path d="M223.046875,58.28125 L223.046875,1.015625 L248.4375,1.015625 C253.541667,1.015625 257.421875,1.52994792 260.078125,2.55859375 C262.734375,3.58723958 264.856771,5.40364583 266.445312,8.0078125 C268.033854,10.6119792 268.828125,13.4895833 268.828125,16.640625 C268.828125,20.703125 267.513021,24.1276042 264.882812,26.9140625 C262.252604,29.7005208 258.190104,31.4713542 252.695312,32.2265625 C254.700521,33.1901042 256.223958,34.140625 257.265625,35.078125 C259.479167,37.109375 261.575521,39.6484375 263.554688,42.6953125 L273.515625,58.28125 L263.984375,58.28125 L256.40625,46.3671875 C254.192708,42.9296875 252.369792,40.2994792 250.9375,38.4765625 C249.505208,36.6536458 248.222656,35.3776042 247.089844,34.6484375 C245.957031,33.9192708 244.804688,33.4114583 243.632812,33.125 C242.773438,32.9427083 241.367188,32.8515625 239.414062,32.8515625 L230.625,32.8515625 L230.625,58.28125 L223.046875,58.28125 Z M230.625,26.2890625 L246.914062,26.2890625 C250.377604,26.2890625 253.085938,25.9309896 255.039062,25.2148438 C256.992188,24.4986979 258.476562,23.3528646 259.492188,21.7773438 C260.507812,20.2018229 261.015625,18.4895833 261.015625,16.640625 C261.015625,13.9322917 260.032552,11.7057292 258.066406,9.9609375 C256.10026,8.21614583 252.994792,7.34375 248.75,7.34375 L230.625,7.34375 L230.625,26.2890625 Z" id="Shape"/>
                <polygon id="Path" points="320.390625 58.28125 320.390625 1.015625 327.96875 1.015625 327.96875 29.4140625 356.40625 1.015625 366.679688 1.015625 342.65625 24.21875 367.734375 58.28125 357.734375 58.28125 337.34375 29.296875 327.96875 38.4375 327.96875 58.28125"/>
                <path d="M411.484375,39.8828125 L418.632812,39.2578125 C418.971354,42.1223958 419.759115,44.4726562 420.996094,46.3085938 C422.233073,48.1445312 424.153646,49.6289062 426.757812,50.7617188 C429.361979,51.8945312 432.291667,52.4609375 435.546875,52.4609375 C438.4375,52.4609375 440.989583,52.03125 443.203125,51.171875 C445.416667,50.3125 447.063802,49.1341146 448.144531,47.6367188 C449.22526,46.1393229 449.765625,44.5052083 449.765625,42.734375 C449.765625,40.9375 449.244792,39.3684896 448.203125,38.0273438 C447.161458,36.6861979 445.442708,35.5598958 443.046875,34.6484375 C441.510417,34.0494792 438.111979,33.1184896 432.851562,31.8554688 C427.591146,30.5924479 423.90625,29.4010417 421.796875,28.28125 C419.0625,26.8489583 417.02474,25.0716146 415.683594,22.9492188 C414.342448,20.8268229 413.671875,18.4505208 413.671875,15.8203125 C413.671875,12.9296875 414.492188,10.2278646 416.132812,7.71484375 C417.773438,5.20182292 420.169271,3.29427083 423.320312,1.9921875 C426.471354,0.690104167 429.973958,0.0390625 433.828125,0.0390625 C438.072917,0.0390625 441.816406,0.72265625 445.058594,2.08984375 C448.300781,3.45703125 450.794271,5.46875 452.539062,8.125 C454.283854,10.78125 455.221354,13.7890625 455.351562,17.1484375 L448.085938,17.6953125 C447.695312,14.0755208 446.373698,11.3411458 444.121094,9.4921875 C441.86849,7.64322917 438.541667,6.71875 434.140625,6.71875 C429.557292,6.71875 426.217448,7.55859375 424.121094,9.23828125 C422.02474,10.9179688 420.976562,12.9427083 420.976562,15.3125 C420.976562,17.3697917 421.71875,19.0625 423.203125,20.390625 C424.661458,21.71875 428.470052,23.0794271 434.628906,24.4726562 C440.78776,25.8658854 445.013021,27.0833333 447.304688,28.125 C450.638021,29.6614583 453.098958,31.6080729 454.6875,33.9648438 C456.276042,36.3216146 457.070312,39.0364583 457.070312,42.109375 C457.070312,45.15625 456.197917,48.0273438 454.453125,50.7226562 C452.708333,53.4179688 450.201823,55.5143229 446.933594,57.0117188 C443.665365,58.5091146 439.986979,59.2578125 435.898438,59.2578125 C430.716146,59.2578125 426.373698,58.5026042 422.871094,56.9921875 C419.36849,55.4817708 416.621094,53.2096354 414.628906,50.1757812 C412.636719,47.1419271 411.588542,43.7109375 411.484375,39.8828125 Z" id="Path"/>
            </g>
            <g id="RECLAIM" transform="translate(0, 558.3047)" fill="#1D2D25" fill-rule="nonzero">
                <path d="M0,119.396484 L0,2.00195312 L52.0507813,2.00195312 C62.5143229,2.00195312 70.46875,3.0563151 75.9140625,5.16503906 C81.359375,7.27376302 85.7102865,10.9973958 88.9667969,16.3359375 C92.2233073,21.6744792 93.8515625,27.5735677 93.8515625,34.0332031 C93.8515625,42.3613281 91.155599,49.3815104 85.7636719,55.09375 C80.3717448,60.8059896 72.0436198,64.4361979 60.7792969,65.984375 C64.889974,67.9596354 68.0130208,69.9082031 70.1484375,71.8300781 C74.6861979,75.9941406 78.983724,81.1992188 83.0410156,87.4453125 L103.460938,119.396484 L83.921875,119.396484 L68.3867188,94.9726562 C63.8489583,87.9257812 60.1119792,82.5338542 57.1757813,78.796875 C54.2395833,75.0598958 51.6103516,72.4440104 49.2880859,70.9492188 C46.9658203,69.4544271 44.6035156,68.4134115 42.2011719,67.8261719 C40.4394531,67.452474 37.5566406,67.265625 33.5527344,67.265625 L15.5351563,67.265625 L15.5351563,119.396484 L0,119.396484 Z M15.5351562,53.8125 L48.9277344,53.8125 C56.0279948,53.8125 61.5800781,53.0784505 65.5839844,51.6103516 C69.5878906,50.1422526 72.6308594,47.7932943 74.7128906,44.5634766 C76.7949219,41.3336589 77.8359375,37.8235677 77.8359375,34.0332031 C77.8359375,28.4811198 75.820638,23.9166667 71.7900391,20.3398438 C67.7594401,16.7630208 61.3932292,14.9746094 52.6914062,14.9746094 L15.5351562,14.9746094 L15.5351562,53.8125 Z" id="Shape"/>
                <polygon id="Path" points="163.515625 119.396484 163.515625 2.00195312 248.398438 2.00195312 248.398438 15.8554688 179.050781 15.8554688 179.050781 51.8105469 243.994141 51.8105469 243.994141 65.5839844 179.050781 65.5839844 179.050781 105.542969 251.121094 105.542969 251.121094 119.396484"/>
                <path d="M401.34375,78.2363281 L416.878906,82.1601562 C413.622396,94.9192708 407.763346,104.648763 399.301758,111.348633 C390.840169,118.048503 380.496745,121.398438 368.271484,121.398438 C355.619141,121.398438 345.329102,118.822591 337.401367,113.670898 C329.473633,108.519206 323.441081,101.058594 319.303711,91.2890625 C315.166341,81.5195312 313.097656,71.0292969 313.097656,59.8183594 C313.097656,47.593099 315.433268,36.929362 320.104492,27.8271484 C324.775716,18.7249349 331.422201,11.8115234 340.043945,7.08691406 C348.66569,2.36230469 358.154948,0 368.511719,0 C380.25651,0 390.132812,2.98958333 398.140625,8.96875 C406.148438,14.9479167 411.727214,23.3561198 414.876953,34.1933594 L399.582031,37.796875 C396.859375,29.2552083 392.908854,23.0358073 387.730469,19.1386719 C382.552083,15.2415365 376.039062,13.2929688 368.191406,13.2929688 C359.169271,13.2929688 351.628581,15.4550781 345.569336,19.7792969 C339.510091,24.1035156 335.252604,29.9091797 332.796875,37.1962891 C330.341146,44.4833984 329.113281,51.9973958 329.113281,59.7382812 C329.113281,69.7213542 330.568034,78.4365234 333.477539,85.8837891 C336.387044,93.3310547 340.911458,98.8964844 347.050781,102.580078 C353.190104,106.263672 359.836589,108.105469 366.990234,108.105469 C375.692057,108.105469 383.059245,105.596354 389.091797,100.578125 C395.124349,95.5598958 399.208333,88.1126302 401.34375,78.2363281 Z" id="Path"/>
                <polygon id="Path" points="480.376953 119.396484 480.376953 2.00195312 495.912109 2.00195312 495.912109 105.542969 553.728516 105.542969 553.728516 119.396484"/>
                <path d="M604.333984,119.396484 L649.417969,2.00195312 L666.154297,2.00195312 L714.201172,119.396484 L696.503906,119.396484 L682.810547,83.8417969 L633.722656,83.8417969 L620.830078,119.396484 L604.333984,119.396484 Z M638.207031,71.1894531 L678.005859,71.1894531 L665.753906,38.6777344 C662.016927,28.8014323 659.240885,20.686849 657.425781,14.3339844 C655.93099,21.8613281 653.822266,29.3352865 651.099609,36.7558594 L638.207031,71.1894531 Z" id="Shape"/>
                <polygon id="Path" points="774.255859 119.396484 774.255859 2.00195312 789.791016 2.00195312 789.791016 119.396484"/>
                <path d="M861.697266,119.396484 L861.697266,2.00195312 L885.080078,2.00195312 L912.867188,85.1230469 C915.429688,92.8639323 917.298177,98.65625 918.472656,102.5 C919.807292,98.2291667 921.889323,91.9563802 924.71875,83.6816406 L952.826172,2.00195312 L973.726562,2.00195312 L973.726562,119.396484 L958.751953,119.396484 L958.751953,21.140625 L924.638672,119.396484 L910.625,119.396484 L876.671875,19.4589844 L876.671875,119.396484 L861.697266,119.396484 Z" id="Path"/>
            </g>
            <rect id="Rectangle" fill="#12B981" x="763.138672" y="743.701172" width="135" height="7"/>
            <rect id="Rectangle" fill="#12B981" x="72.1386719" y="743.701172" width="135" height="7"/>
            <polygon id="Path-2" fill="#12B981" points="241.162109 90.03125 331.289062 126.023438 331.289062 457.404297 241.162109 483.107422"/>
            <polygon id="Path-3" fill="#12B981" points="268.369141 129.210938 313.314453 145.644531 313.314453 265.181641 268.369141 262.347656"/>
            <path d="M264.369141,123.489403 L269.742743,125.454181 L314.688056,141.887775 L317.314453,142.848079 L317.314453,269.441801 L313.062737,269.173713 L268.117424,266.339728 L264.369141,266.103384 L264.369141,262.347656 L264.369141,123.489403 Z M272.369141,134.931938 L272.369141,258.590937 L309.314141,260.920938 L309.314141,148.440938 L272.369141,134.931938 Z" id="Path-3" fill="#2F2F2F" fill-rule="nonzero"/>
            <polygon id="Path-3" fill="#12B981" transform="translate(290.8418, 375.1963) scale(-1, 1) rotate(180) translate(-290.8418, -375.1963)" points="268.369141 307.210938 313.314453 316.251953 313.314453 443.181641 268.369141 443.181641"/>
            <path d="M264.369141,303.210938 L269.157963,304.174238 L314.103276,313.215253 L317.314453,313.8612 L317.314453,448.066389 L264.369141,448.066389 L264.369141,444.066389 L264.369141,303.210938 Z M272.369141,312.979686 L272.369141,440.065686 L309.314141,440.065686 L309.314141,320.411686 L272.369141,312.979686 Z" id="Path-3" fill="#2F2F2F" fill-rule="nonzero" transform="translate(290.8418, 375.6387) scale(-1, 1) rotate(180) translate(-290.8418, -375.6387)"/>
            <polygon id="Path-4" stroke="#979797" fill="#12B981" points="706.466797 334.931641 741.138672 424.556641 840.525391 177.130859 766.966797 177.130859"/>
            <path d="M166.669922,483.107422 L234.509766,483.107422 L234.509766,61.7832031 L362.42041,61.7832031 C375.900507,63.1668402 385.534459,64.9900824 391.322266,67.2529297 C422.826493,79.5700748 439.310547,105.453528 439.310547,140.701172 C439.310547,157.710787 434.430016,171.514778 427.101562,183.276367 C418.626423,196.878297 406.465768,207.533652 388.168945,213.795898 C383.034465,215.553219 374.45162,217.366207 362.42041,219.234863 L362.42041,260.034668 L472.332031,484.381836 L526.463867,484.381836 L603.701172,300.84375 L672.433594,484.381836 L749.353516,484.381836 L631.548828,177.130859 L549.009766,177.130859 L574.037109,241.873047 L510.333008,397.249023 L441.924805,256.451172 C452.925094,246.747088 460.398076,239.661801 464.34375,235.195313 C470.718143,227.979523 474.977933,219.956795 478.138672,215.216797 C487.642921,200.963761 494.178867,185.173327 497.134766,172.763672 C499.34898,163.467809 501.235907,151.879665 501.03418,140.701172 C500.93009,134.933186 498.824053,106.73017 493.86377,94.3754883 C493.297459,92.9649677 488.424427,80.4449257 487.388672,78.0776367 C485.007374,72.6350178 480.495818,65.3796572 476.887695,59.2451172 C473.3827,53.2859134 468.877402,48.4035062 468.20752,47.5419922 C466.525799,45.3791868 463.678195,43.083984 461.300293,40.590332 C453.180781,32.0755855 441.175473,22.1346446 425.387207,14.6079102 C420.688838,12.3680585 414.029977,8.85029221 405.282715,6.55566406 C402.059751,5.71019917 397.384183,4.34447142 391.322266,3.26806641 C387.181016,2.53271123 379.48961,1.44335577 368.248047,0 L166.669922,0 L166.669922,483.107422 Z" id="Path-5" fill="#1D2B25"/>
            <polygon id="Path-6" fill="#1D2B25" points="241.162109 68.1425781 351.827148 110.607422 351.827148 459.195312 331.289062 459.195312 331.289062 126.023438 241.162109 90.03125"/>
        </g>
    </g>
</svg>
`

const subtitle = ref('Professionally removed, complete set, ready to install again')

const intro = ref(
    `${props.product.description}`
)

const delivery = ref(
    'Delivery may be available depending on location. Please message first to confirm delivery pricing.'
)

const footer = ref(
    'Please message with any questions before purchasing.'
)

const features = ref([
    'Professionally removed',
    'Ready for reinstallation',
    'Please check measurements before buying',
])

const escapeHtml = (text) => {
    return String(text ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
}

const featuresText = ref(features.value.join('\n'))

const productImage = computed(() => {
    return props.product.primary_image
        ? `${window.location.origin}/storage/${props.product.primary_image.path}`
        : ''
})

const mmValue = (v) => `${Math.round(Number(v || 0))} mm`
const cmValue = (v) => `${(Number(v || 0) / 10).toFixed(1).replace('.0', '')} cm`
const inchValue = (v) => `${(Number(v || 0) / 25.4).toFixed(2).replace('.00', '')} in`
const ftValue = (v) => `${(Number(v || 0) / 304.8).toFixed(2).replace('.00', '')} ft`

const measurementCss = `
<style>
.ebay-unit-value{display:none;}
#unit-mm:checked ~ .measurement-box .value-mm{display:inline;}
#unit-cm:checked ~ .measurement-box .value-cm{display:inline;}
#unit-inch:checked ~ .measurement-box .value-inch{display:inline;}
#unit-ft:checked ~ .measurement-box .value-ft{display:inline;}

#unit-mm:checked ~ .unit-tabs label[for="unit-mm"],
#unit-cm:checked ~ .unit-tabs label[for="unit-cm"],
#unit-inch:checked ~ .unit-tabs label[for="unit-inch"],
#unit-ft:checked ~ .unit-tabs label[for="unit-ft"]{
    background:#12B981!important;
    color:#fff!important;
}
</style>
`

const selectedParts = computed(() => {
    return props.product.attributes
        ?.filter(attr => attr.group?.slug === 'parts')
        ?.map(attr => attr.name)
        ?? []
})

const dynamicFeatures = computed(() => {
    const items = [...featuresText.value.split('\n').filter(item => item.trim() !== '')]

    const parts = selectedParts.value

    if (parts.includes('Frame')) {
        items.push('Frame included')
    }

    const includedParts = parts.filter(part =>
        ['Keys', 'Locks', 'Cill', 'Glass', 'Blinds', 'Vents'].includes(part)
    )

    if (includedParts.length) {
        items.push(`Item comes with: ${includedParts.join(', ')}`)
    }

    return items
})

const getAttribute = (groupSlug) => {
    return props.product.attributes?.find(
        attr => attr.group?.slug === groupSlug
    )?.name ?? '-'
}

const getConfiguration = () => {
    return getAttribute('configuration')
}

const htmlOutput = computed(() => {
   const featureItems = dynamicFeatures.value

    return `
<div style="font-family:Arial,Helvetica,sans-serif;background:#f8f8f4;color:#1D2B25;">

    <div style="text-align:center;padding:30px 20px;background:#ffffff;">
        <div style="display:inline-block;width:220px;max-width:90%;">
            ${logoSvg}
        </div>
        <div style="margin-top:12px;color:#12B981;font-size:13px;letter-spacing:3px;font-weight:bold;">
            RECLAIMED. REFURBISHED. REUSED.
        </div>
    </div>

    <div style="background:linear-gradient(135deg,#062f25,#1D2B25);color:#fff;padding:45px 30px;">
        <div style="max-width:1000px;margin:0 auto;">
            <div style="display:inline-block;background:rgba(18,185,129,0.18);color:#12B981;padding:8px 16px;border-radius:999px;font-size:13px;font-weight:bold;margin-bottom:18px;">
                SAVED FROM WASTE
            </div>

            <h1 style="margin:0;font-size:44px;line-height:1.05;font-weight:800;">
                ${escapeHtml(props.product.title)}
            </h1>

            <p style="margin:18px 0 0;font-size:18px;line-height:1.6;color:#d7eee7;max-width:700px;">
                ${escapeHtml(subtitle.value)}
            </p>
        </div>
    </div>

    ${productImage.value ? `
    <div style="max-width:1000px;margin:30px auto 0;padding:0 20px;">
        <img src="${productImage.value}" style="width:100%;max-height:520px;object-fit:cover;border-radius:18px;display:block;">
    </div>
    ` : ''}

   

    ${measurementCss}

    <div style="max-width:1000px;margin:30px auto;padding:0 20px;">
        <div style="background:#ffffff;border-radius:18px;padding:30px;border:1px solid #e5e5e5;">

            <input type="radio" name="unit" id="unit-mm" checked style="display:none;">
            <input type="radio" name="unit" id="unit-cm" style="display:none;">
            <input type="radio" name="unit" id="unit-inch" style="display:none;">
            <input type="radio" name="unit" id="unit-ft" style="display:none;">

            <h2 style="margin:0 0 20px;text-align:center;font-size:20px;letter-spacing:2px;">
                FRAME MEASUREMENTS
            </h2>

            <div class="unit-tabs" style="display:flex;justify-content:center;gap:8px;margin-bottom:28px;flex-wrap:wrap;">
                <label for="unit-mm" style="padding:8px 18px;border-radius:999px;background:#f1f1f1;cursor:pointer;font-size:13px;font-weight:bold;">MM</label>
                <label for="unit-cm" style="padding:8px 18px;border-radius:999px;background:#f1f1f1;cursor:pointer;font-size:13px;font-weight:bold;">CM</label>
                <label for="unit-inch" style="padding:8px 18px;border-radius:999px;background:#f1f1f1;cursor:pointer;font-size:13px;font-weight:bold;">INCH</label>
                <label for="unit-ft" style="padding:8px 18px;border-radius:999px;background:#f1f1f1;cursor:pointer;font-size:13px;font-weight:bold;">FT</label>
            </div>

            <div class="measurement-box" style="display:flex;gap:15px;text-align:center;">

                <div style="flex:1;border-right:1px solid #ddd;">
                    <div style="font-size:12px;font-weight:bold;letter-spacing:2px;">WIDTH</div>
                    <div style="font-size:34px;color:#12B981;font-weight:bold;margin-top:10px;">
                        <span class="ebay-unit-value value-mm">${mmValue(props.product.width)}</span>
                        <span class="ebay-unit-value value-cm">${cmValue(props.product.width)}</span>
                        <span class="ebay-unit-value value-inch">${inchValue(props.product.width)}</span>
                        <span class="ebay-unit-value value-ft">${ftValue(props.product.width)}</span>
                    </div>
                </div>

                <div style="flex:1;border-right:1px solid #ddd;">
                    <div style="font-size:12px;font-weight:bold;letter-spacing:2px;">HEIGHT</div>
                    <div style="font-size:34px;color:#12B981;font-weight:bold;margin-top:10px;">
                        <span class="ebay-unit-value value-mm">${mmValue(props.product.height)}</span>
                        <span class="ebay-unit-value value-cm">${cmValue(props.product.height)}</span>
                        <span class="ebay-unit-value value-inch">${inchValue(props.product.height)}</span>
                        <span class="ebay-unit-value value-ft">${ftValue(props.product.height)}</span>
                    </div>
                </div>

                <div style="flex:1;">
                    <div style="font-size:12px;font-weight:bold;letter-spacing:2px;">DEPTH</div>
                    <div style="font-size:34px;color:#12B981;font-weight:bold;margin-top:10px;">
                        <span class="ebay-unit-value value-mm">${mmValue(props.product.depth)}</span>
                        <span class="ebay-unit-value value-cm">${cmValue(props.product.depth)}</span>
                        <span class="ebay-unit-value value-inch">${inchValue(props.product.depth)}</span>
                        <span class="ebay-unit-value value-ft">${ftValue(props.product.depth)}</span>
                    </div>
                </div>

            </div>
        </div>
    </div>

     <div style="max-width:1000px;margin:30px auto;padding:0 20px;">
        <div style="background:#ffffff;border-radius:18px;padding:30px;border:1px solid #e5e5e5;">
            <h2 style="margin:0 0 15px;font-size:22px;letter-spacing:1px;">About This Item</h2>
            <p style="margin:0;font-size:16px;line-height:1.8;color:#333;">
                ${escapeHtml(intro.value)}
            </p>
        </div>
    </div>

    <div style="max-width:1000px;margin:30px auto;padding:0 20px;">
        <div style="background:#ffffff;border-radius:18px;padding:30px;border:1px solid #e5e5e5;">
            <h2 style="margin:0 0 20px;font-size:22px;">Key Features</h2>

            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
                ${featureItems.map(item => `
                    <div style="background:#f8f8f4;border:1px solid #e5e5e5;border-radius:12px;padding:16px;font-size:15px;line-height:1.5;">
                        <strong style="color:#12B981;">✓</strong> ${escapeHtml(item)}
                    </div>
                `).join('')}
            </div>
        </div>
    </div>

    <div style="max-width:1000px;margin:30px auto;padding:0 20px;">
    <div style="background:#ffffff;border-radius:18px;padding:30px;border:1px solid #e5e5e5;">

        <h2 style="margin:0 0 20px;font-size:22px;">
            Specifications
        </h2>

        <table style="width:100%;border-collapse:collapse;font-size:15px;">

            <tr>
                <td style="padding:14px;border-bottom:1px solid #ececec;font-weight:bold;width:220px;">
                    Colour
                </td>
                <td style="padding:14px;border-bottom:1px solid #ececec;">
                    ${escapeHtml(getAttribute('colour'))}
                </td>
            </tr>

            <tr>
                <td style="padding:14px;border-bottom:1px solid #ececec;font-weight:bold;">
                    Brand
                </td>
                <td style="padding:14px;border-bottom:1px solid #ececec;">
                    ${escapeHtml(getAttribute('brand'))}
                </td>
            </tr>

            <tr>
                <td style="padding:14px;border-bottom:1px solid #ececec;font-weight:bold;">
                    Material
                </td>
                <td style="padding:14px;border-bottom:1px solid #ececec;">
                    ${escapeHtml(getAttribute('material'))}
                </td>
            </tr>

            <tr>
                <td style="padding:14px;border-bottom:1px solid #ececec;font-weight:bold;">
                    Configuration
                </td>
                <td style="padding:14px;border-bottom:1px solid #ececec;">
                    ${escapeHtml(getConfiguration())}
                </td>
            </tr>

            <tr>
                <td style="padding:14px;font-weight:bold;">
                    Traffic Door
                </td>
                <td style="padding:14px;">
                    ${escapeHtml(getAttribute('traffic-door'))}
                </td>
            </tr>

            <tr>
                <td style="padding:14px;font-weight:bold;">
                    Condition
                </td>
                <td style="padding:14px;">
                    ${escapeHtml(getAttribute('condition'))}
                </td>
            </tr>

        </table>

    </div>
</div>

    <div style="max-width:1000px;margin:30px auto;padding:0 20px;">
        <div style="display:flex;gap:20px;">
            <div style="flex:1;background:#ffffff;border-radius:18px;padding:25px;border:1px solid #e5e5e5;">
                <h2 style="margin:0 0 12px;font-size:20px;">Delivery / Collection</h2>
                <p style="margin:0;font-size:15px;line-height:1.7;color:#333;">
                    ${escapeHtml(delivery.value)}
                </p>
            </div>

            <div style="flex:1;background:#ffffff;border-radius:18px;padding:25px;border:1px solid #e5e5e5;">
                <h2 style="margin:0 0 12px;font-size:20px;">Additional Notes</h2>
                <p style="margin:0;font-size:15px;line-height:1.7;color:#333;">
                    ${escapeHtml(footer.value)}
                </p>
            </div>
        </div>
    </div>

    <div style="background:#062f25;color:#fff;text-align:center;padding:28px 20px;margin-top:40px;">
        <div style="color:#12B981;font-size:24px;">♻</div>
        <div style="font-size:14px;letter-spacing:2px;font-weight:bold;">
            GIVING DOORS AND WINDOWS A SECOND LIFE.
        </div>
        <div style="font-size:12px;letter-spacing:2px;color:#bdeee0;margin-top:8px;">
            THANK YOU FOR CHOOSING SUSTAINABLE.
        </div>
    </div>

</div>
`
})

const copyHtml = async () => {
    await navigator.clipboard.writeText(htmlOutput.value)
    alert('eBay HTML copied')
}
</script>

<template>

     <button type="button" @click="copyHtml" class="bg-green-500 px-4 py-2 rounded font-bold">
            Copy eBay HTML
        </button>
    <div class="p-6 bg-white shadow rounded-xl mt-10">
        <!-- <h2 class="font-semibold mb-4">eBay HTML</h2>

        <input v-model="subtitle" class="w-full border p-2 mb-3" placeholder="Subtitle" />

        <textarea v-model="intro" class="w-full border p-2 mb-3" rows="4" />

        <textarea
            v-model="features"
            class="w-full border p-2 mb-3"
            rows="5"
        />

        <textarea v-model="delivery" class="w-full border p-2 mb-3" rows="3" />
        <textarea v-model="footer" class="w-full border p-2 mb-3" rows="3" />

       

        <textarea class="w-full border p-2 mt-4 h-64" :value="htmlOutput" readonly /> -->

        <div class="mt-6 border p-4">
            <div v-html="htmlOutput"></div>
        </div>
    </div>
</template>