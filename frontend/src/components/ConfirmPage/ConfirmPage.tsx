import { useEffect, useState } from 'react'
import { useNavigate, useSearchParams } from 'react-router-dom'
import { confirm } from '../../utils/api'
//@ts-ignore
import Loader from '../LoaderPage/LoaderPage'

function ConfirmPage() {
	const [searchParams] = useSearchParams()
	const navigate = useNavigate()
	const [status, setStatus] = useState<'loading' | 'ok' | 'error'>('loading')

	const token = searchParams.get('token')

	useEffect(() => {
		if (!token) {
			navigate('/auth')
			return
		}

		const fetchConfirmation = async () => {
			try {
				const result = await confirm(token)
				if (result['status'] === 'ok') {
					setStatus('ok')
				} else {
					setStatus('error')
				}
			} catch (error) {
				setStatus('error')
			}
		}

		fetchConfirmation()
	}, [token, navigate])

	if (status === 'ok') {
		navigate('/messenger')
		return null
	}

	if (status === 'error') {
		navigate('/auth')
		return null
	}

	return <Loader />
}

export default ConfirmPage
