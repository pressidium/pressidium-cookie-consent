(details => {
  if(pressidiumCCClientDetails.settings.auto_language === "browser"){
    for (var k in pressidiumCCClientDetails.settings.languages) {
      if(k.indexOf('-') !== -1){
        pressidiumCCClientDetails.settings.languages[k.split("-")[0]] = pressidiumCCClientDetails.settings.languages[k];
        delete pressidiumCCClientDetails.settings.languages[k];
      }
    }
  }
})(pressidiumCCClientDetails);
