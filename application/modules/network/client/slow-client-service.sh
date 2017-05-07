#!/bin/sh
BASEDIR=$(dirname $0)
PIDFILE="${BASEDIR}/logs/.pidfile"
PHP=$(which php-cli)

echo $$ > ${PIDFILE}

#${PHP} -f ${BASEDIR}/slow-client.php settings >> ${BASEDIR}/logs/settings.log 2>&1
${PHP} -f ${BASEDIR}/slow-client.php settings 2>&1
sleep 1s

while :
do
    NEWPID=$$;
    OLDPID="$(cat ${PIDFILE})"
    if [[ $NEWPID != $OLDPID ]]
    then
        break;
    fi

    #LOGFILE="api-log-"$(date +%Y%m%d)".log"

    #echo $$ > ${PIDFILE}

    #${PHP} -f ${BASEDIR}/slow-client.php put_profiles >> ${BASEDIR}/logs/${LOGFILE} 2>&1
    ${PHP} -f ${BASEDIR}/slow-client.php put_profiles 2>&1

    NEWPID=$$;
    OLDPID="$(cat ${PIDFILE})"
    if [[ $NEWPID != $OLDPID ]]
    then
        break;
    fi

    #echo $$ > ${PIDFILE}

    sleep 1s

    #${PHP} -f ${BASEDIR}/slow-client.php get_profiles_status >> ${BASEDIR}/logs/${LOGFILE} 2>&1
    ${PHP} -f ${BASEDIR}/slow-client.php get_profiles_status 2>&1

    NEWPID=$$;
    OLDPID="$(cat ${PIDFILE})"
    if [[ $NEWPID != $OLDPID ]]
    then
        break;
    fi

    #echo $$ > ${PIDFILE}

    sleep 1s

    #${PHP} -f ${BASEDIR}/slow-client.php get_profiles >> ${BASEDIR}/logs/${LOGFILE} 2>&1
    ${PHP} -f ${BASEDIR}/slow-client.php get_profiles 2>&1

    NEWPID=$$;
    OLDPID="$(cat ${PIDFILE})"
    if [[ $NEWPID != $OLDPID ]]
    then
        break;
    fi

    #echo $$ > ${PIDFILE}

    sleep 1s

    #${PHP} -f ${BASEDIR}/slow-client.php put_profiles_status >> ${BASEDIR}/logs/${LOGFILE} 2>&1
    ${PHP} -f ${BASEDIR}/slow-client.php put_profiles_status 2>&1

    NEWPID=$$;
    OLDPID="$(cat ${PIDFILE})"
    if [[ $NEWPID != $OLDPID ]]
    then
        break;
    fi

    #echo $$ > ${PIDFILE}

    sleep 1s

    #${PHP} -f ${BASEDIR}/slow-client.php put_removed >> ${BASEDIR}/logs/${LOGFILE} 2>&1
    ${PHP} -f ${BASEDIR}/slow-client.php put_removed 2>&1

    NEWPID=$$;
    OLDPID="$(cat ${PIDFILE})"
    if [[ $NEWPID != $OLDPID ]]
    then
        break;
    fi

    #echo $$ > ${PIDFILE}

    sleep 1s

    #${PHP} -f ${BASEDIR}/slow-client.php get_removed >> ${BASEDIR}/logs/${LOGFILE} 2>&1
    ${PHP} -f ${BASEDIR}/slow-client.php get_removed 2>&1

    NEWPID=$$;
    OLDPID="$(cat ${PIDFILE})"
    if [[ $NEWPID != $OLDPID ]]
    then
        break;
    fi

    #echo $$ > ${PIDFILE}

    sleep 1s

    #${PHP} -f ${BASEDIR}/slow-client.php put_removed_status >> ${BASEDIR}/logs/${LOGFILE} 2>&1
    ${PHP} -f ${BASEDIR}/slow-client.php put_removed_status 2>&1

    NEWPID=$$;
    OLDPID="$(cat ${PIDFILE})"
    if [[ $NEWPID != $OLDPID ]]
    then
        break;
    fi

    #echo $$ > ${PIDFILE}

    sleep 1s

    #${PHP} -f ${BASEDIR}/slow-client.php put_updated >> ${BASEDIR}/logs/${LOGFILE} 2>&1
    ${PHP} -f ${BASEDIR}/slow-client.php put_updated 2>&1

    NEWPID=$$;
    OLDPID="$(cat ${PIDFILE})"
    if [[ $NEWPID != $OLDPID ]]
    then
        break;
    fi

    #echo $$ > ${PIDFILE}

    sleep 1s

    #${PHP} -f ${BASEDIR}/slow-client.php get_updated >> ${BASEDIR}/logs/${LOGFILE} 2>&1
    ${PHP} -f ${BASEDIR}/slow-client.php get_updated 2>&1

    NEWPID=$$;
    OLDPID="$(cat ${PIDFILE})"
    if [[ $NEWPID != $OLDPID ]]
    then
        break;
    fi

    sleep 30s
done
