#!/usr/bin/env bash
sed=/usr/bin/sed
readme_path="../build_readmes/"
changelog_source=${readme_path}current.txt
incomplete_out=tmp.txt
json_out=json_changelog.txt
readme_out=readme_changelog.txt
short_name="e20r-sequences"
version=$(egrep "^Version:" ../${short_name}.php | awk '{print $2}')
json_header="<h3>${version}</h3><ol>"
json_footer="</ol>"
readme_header="== ${version} =="
###########
#
# Create a metadata.json friendly changelog entry for the current ${version}
#
${sed} -e"s/\"/\'/g" -e"s/.*/\<li\>&\<\/li\>/" ${changelog_source} > ${readme_path}${incomplete_out}
echo -n ${json_header} > ${readme_path}${json_out}
cat ${readme_path}${incomplete_out} | tr -d '\n' >> ${readme_path}${json_out}
echo -n ${json_footer} >> ${readme_path}${json_out}
rm ${readme_path}${incomplete_out}
###########
#
# Create a README.txt friendly changelog entry for the current ${version}
#
echo ${readme_header} > ${readme_path}${readme_out}
echo '' >> ${readme_path}${readme_out}
${sed} -e"s/\"/\'/g" -e"s/.*/\*\ &/" ${changelog_source} >> ${readme_path}${readme_out}
echo '' >> ${readme_path}${readme_out}
