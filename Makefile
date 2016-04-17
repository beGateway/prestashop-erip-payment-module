all:
	if [[ -e begatewayerip.zip ]]; then rm begatewayerip.zip; fi
	zip -r begatewayerip.zip begatewayerip 
