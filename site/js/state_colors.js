function getStates(region) {
   var regionColors = ['#5c7981', '#643c18', '#b2721a', '#dbd543', '#679a61', '#e28e45', '#508dac', '#a48f70',
       '#9dcbdb', '#d99b9c'];
   var regionStates = [
      ['CT', 'MA', 'ME', 'NH', 'RI', 'VT', ],
      ['NJ', 'NY', ],
      ['DE', 'MD', 'PA', 'VA', 'WV', ],
      ['AL', 'FL', 'GA', 'KY', 'MS', 'NC', 'SC', 'TN', ],
      ['IL', 'IN', 'MI', 'MN', 'OH', 'WI', ],
      ['AR', 'LA', 'TX', 'NM', 'OK', ],
      ['IA', 'KS', 'MO', 'NE', ],
      ['CO', 'MT', 'ND', 'SD', 'UT', 'WY', ],
      ['AZ', 'CA', 'HI', 'NV', ],
      ['AK', 'ID', 'OR', 'WA', ] // d99b9c
   ];
   
   var style = {};
   
   // Case 1: region is a single state.
   if (region == 11) { // PA
      style['PA'] = {fill: '#b2721a'};
   } else if (region == 12) { // GA
      style['GA'] = {fill: '#dbd543'};
   } else if (region == 13) { // DC
      style['DC'] = {fill: '#b2721a'};
   } else if (region == 14) { // TX
      style['TX'] = {fill: '#e28e45'};
   } else if (region == 15) { // OR
      style['OR'] = {fill: '#d99b9c'};
   } else if (region == 16) { // WY
      style['WY'] = {fill: '#a48f70'};
   } else if (region == 17) { // CT
      style['CT'] = {fill: '#5c7981'};
   } else if (region == 18) { // ME
      style['ME'] = {fill: '#5c7981'};
   } else if (region == 19) { // NH
      style['NH'] = {fill: '#5c7981'};
   } else if (region == 20) { // RI
      style['RI'] = {fill: '#5c7981'};
   } else if (region == 21) { // VT
      style['VT'] = {fill: '#5c7981'};
   } else if (region == 22) { // NJ
      style['NJ'] = {fill: '#643c18'};
   } else if (region == 23) { // NY
      style['NY'] = {fill: '#643c18'};
   } else if (region == 24) { // DE
      style['DE'] = {fill: '#b2721a'};
   } else if (region == 25) { // MD
      style['MD'] = {fill: '#b2721a'};
   } else if (region == 26) { // VA
      style['VA'] = {fill: '#b2721a'};
   } else if (region == 27) { // WV
      style['WV'] = {fill: '#b2721a'};
   } else if (region == 28) { // AL
      style['AL'] = {fill: '#dbd543'};
   } else if (region == 30) { // KY
      style['KY'] = {fill: '#dbd543'};
   } else if (region == 31) { // MS
      style['MS'] = {fill: '#dbd543'};
   } else if (region == 32) { // NC
      style['NC'] = {fill: '#dbd543'};
   } else if (region == 33) { // SC
      style['SC'] = {fill: '#dbd543'};
   } else if (region == 34) { // TN
      style['TN'] = {fill: '#dbd543'};
   } else if (region == 35) { // IL
      style['IL'] = {fill: '#679a61'};
   } else if (region == 36) { // IN
      style['IN'] = {fill: '#679a61'};
   } else if (region == 37) { // MI
      style['MI'] = {fill: '#679a61'};
   } else if (region == 38) { // MN
      style['MN'] = {fill: '#679a61'};
   } else if (region == 39) { // OH
      style['OH'] = {fill: '#679a61'};
   } else if (region == 40) { // WI
      style['WI'] = {fill: '#679a61'};
   } else if (region == 41) { // AR
      style['AR'] = {fill: '#e28e45'};
   } else if (region == 42) { // NM
      style['NM'] = {fill: '#e28e45'};
   } else if (region == 43) { // OK
      style['OK'] = {fill: '#e28e45'};
   } else if (region == 44) { // MA
      style['MA'] = {fill: '#5c7981'};
   } else if (region == 45) { // IA
      style['IA'] = {fill: '#508dac'};
   } else if (region == 46) { // KS
      style['KS'] = {fill: '#508dac'};
   } else if (region == 47) { // MO
      style['MO'] = {fill: '#508dac'};
   } else if (region == 48) { // NE
      style['NE'] = {fill: '#508dac'};
   } else if (region == 49) { // CO
      style['CO'] = {fill: '#a48f70'};
   } else if (region == 50) { // MT
      style['MT'] = {fill: '#a48f70'};
   } else if (region == 51) { // ND
      style['ND'] = {fill: '#a48f70'};
   } else if (region == 52) { // SD
      style['SD'] = {fill: '#a48f70'};
   } else if (region == 53) { // UT
      style['UT'] = {fill: '#a48f70'};
   } else if (region == 54) { // AZ
      style['AZ'] = {fill: '#9dcbdb'};
   } else if (region == 55) { // CA
      style['CA'] = {fill: '#9dcbdb'};
   } else if (region == 56) { // HI
      style['HI'] = {fill: '#9dcbdb'};
   } else if (region == 57) { // NV
      style['NV'] = {fill: '#9dcbdb'};
   } else if (region == 58) { // ID
      style['ID'] = {fill: '#d99b9c'};
   } else if (region == 59) { // AK
      style['AK'] = {fill: '#d99b9c'};
   } else if (region == 60) { // WA
      style['WA'] = {fill: '#d99b9c'};
   } else if (region == 61) { // LA
      style['LA'] = {fill: '#e28e45'};
   } else if (region == 62) { // PR
      style['PR'] = {fill: '#b2721a'};
   } else if (region == 63) { // VI
      style['VI'] = {fill: '#b2721a'};
   } else if (region == 64) { // JFK
      style['NY'] = {fill: '#643c18'};
   } else if (region == 0) { // Case 2: region is national.
      var num_hhs_regions = 10;
      for(var i = 0; i < num_hhs_regions; i++) {
         for(var j = 0; j < regionStates[i].length; j++) {
            style[regionStates[i][j]] = {fill: regionColors[i]};
         }
      }
   } else { // Case 3: region is a HHS region that contains multiple states.
      var states = regionStates[region - 1]; // here, hhs1 corresponds to region 1, which is the 0th index in regionStates.
      var region_color = regionColors[region - 1];
      for(var i = 0; i < states.length; i++) {
         style[states[i]] = {fill: region_color};
      }
   }
      
   return style;
}
